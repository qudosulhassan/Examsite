<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SettingsAdminController extends Controller
{
    /**
     * Display the Settings Center.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Defaults if not set
        $settings['site_name'] = $settings['site_name'] ?? config('app.name', 'Exam Topics Base');
        $settings['site_tagline'] = $settings['site_tagline'] ?? 'Pass Like a Ninja. First Attempt Guaranteed.';
        $settings['site_url'] = $settings['site_url'] ?? config('app.url', 'http://127.0.0.1:8000');
        $settings['contact_email'] = $settings['contact_email'] ?? 'contact@examtopicsbase.com';
        $settings['support_email'] = $settings['support_email'] ?? 'support@examtopicsbase.com';
        $settings['default_timezone'] = $settings['default_timezone'] ?? config('app.timezone', 'UTC');
        $settings['default_currency'] = $settings['default_currency'] ?? 'USD';

        // Payment status & config checks (read-only / safe)
        $paymentInfo = [
            'stripe_configured' => !empty(config('services.stripe.key')) && config('services.stripe.key') !== 'pk_test_placeholder',
            'stripe_mode' => str_starts_with(config('services.stripe.key') ?? '', 'pk_live_') ? 'Live' : 'Test / Sandbox',
            'stripe_public_key' => config('services.stripe.key') ? substr(config('services.stripe.key'), 0, 14) . '...' : 'Not configured',
            'stripe_webhook_configured' => !empty(config('services.stripe.webhook_secret')),
            'paypal_configured' => !empty(config('services.paypal.client_id')) && config('services.paypal.client_id') !== 'sandbox_client_id_placeholder',
            'paypal_mode' => ucfirst(config('services.paypal.mode', 'sandbox')),
        ];

        // Last updated info from Setting model / Audit logs
        $lastSetting = Setting::orderBy('updated_at', 'desc')->first();
        $lastUpdatedTime = $lastSetting ? $lastSetting->updated_at : null;
        
        $lastAudit = AuditLog::where('action', 'settings_updated')->latest()->first();
        $lastUpdatedBy = $lastAudit && $lastAudit->admin ? $lastAudit->admin->name : 'Administrator';

        // Parse plans
        $rawPlans = $settings['subscription_plans'] ?? '[]';
        $plans = json_decode($rawPlans, true);
        if (!is_array($plans)) {
            $plans = [];
        }

        return view('admin.settings', compact(
            'settings',
            'plans',
            'paymentInfo',
            'lastUpdatedTime',
            'lastUpdatedBy'
        ));
    }

    /**
     * Update settings with validation, authorization, and audit logging.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        // Validate all incoming fields
        $validated = $request->validate([
            // General
            'site_name' => 'nullable|string|max:100',
            'site_tagline' => 'nullable|string|max:255',
            'site_url' => 'nullable|url|max:255',
            'contact_email' => 'nullable|email|max:100',
            'support_email' => 'nullable|email|max:100',
            'default_timezone' => 'nullable|string|max:50',
            'default_currency' => 'nullable|string|max:10',

            // Contact & Social
            'contact_phone' => 'nullable|string|max:50',
            'contact_whatsapp' => 'nullable|string|max:50',
            'business_address' => 'nullable|string|max:500',
            'social_facebook' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_linkedin' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'social_telegram' => 'nullable|url|max:255',

            // SEO
            'default_seo_title' => 'nullable|string|max:255',
            'default_meta_description' => 'nullable|string|max:500',
            'default_meta_keywords' => 'nullable|string|max:500',
            'canonical_site_url' => 'nullable|url|max:255',
            'robots_setting' => 'nullable|string|max:50',
            'default_og_title' => 'nullable|string|max:255',
            'default_og_description' => 'nullable|string|max:500',

            // Promotion Banner
            'home_banner_active' => 'nullable|in:0,1',
            'home_banner_text' => 'nullable|string|max:500',
            'home_banner_link' => 'nullable|string|max:255',
            'home_banner_button_text' => 'nullable|string|max:50',
            'home_banner_coupon' => 'nullable|string|max:50',
            'home_banner_start_date' => 'nullable|date',
            'home_banner_end_date' => 'nullable|date|after_or_equal:home_banner_start_date',

            // Subscriptions
            'subscription_plans' => 'nullable|string',

            // Email
            'mail_from_name' => 'nullable|string|max:100',
            'mail_from_address' => 'nullable|email|max:100',
            'order_notification_email' => 'nullable|email|max:100',
            'admin_notification_email' => 'nullable|email|max:100',

            // Maintenance
            'maintenance_mode' => 'nullable|in:true,false',
            'maintenance_message' => 'nullable|string|max:1000',
            'maintenance_return_time' => 'nullable|string|max:100',

            // Security
            'session_timeout_minutes' => 'nullable|integer|min:15|max:1440',
            'max_login_attempts' => 'nullable|integer|min:3|max:20',
            'password_min_length' => 'nullable|integer|min:8|max:32',
        ], [
            'home_banner_end_date.after_or_equal' => 'Promotion banner end date must be on or after the start date.',
            'contact_email.email' => 'Please enter a valid Contact Email address.',
            'support_email.email' => 'Please enter a valid Support Email address.',
            'mail_from_address.email' => 'Please enter a valid Mail From Email address.',
            'order_notification_email.email' => 'Please enter a valid Order Notification Email address.',
            'admin_notification_email.email' => 'Please enter a valid Admin Notification Email address.',
        ]);

        // Validate subscription plans if provided as JSON
        if ($request->filled('subscription_plans')) {
            $decoded = json_decode($request->subscription_plans, true);
            if (!is_array($decoded)) {
                return back()->withInput()->withErrors(['subscription_plans' => 'Invalid subscription plans JSON structure.']);
            }
            foreach ($decoded as $idx => $plan) {
                if (empty($plan['name'])) {
                    return back()->withInput()->withErrors(['subscription_plans' => "Plan #" . ($idx + 1) . " must have a name."]);
                }
                if (isset($plan['price_monthly']) && (!is_numeric($plan['price_monthly']) || (float)$plan['price_monthly'] < 0)) {
                    return back()->withInput()->withErrors(['subscription_plans' => "Plan '{$plan['name']}' monthly price must be a non-negative number."]);
                }
                if (isset($plan['price_annual']) && (!is_numeric($plan['price_annual']) || (float)$plan['price_annual'] < 0)) {
                    return back()->withInput()->withErrors(['subscription_plans' => "Plan '{$plan['name']}' annual price must be a non-negative number."]);
                }
            }
        }

        $allPrevious = Setting::all()->pluck('value', 'key')->toArray();
        $changes = [];

        foreach ($validated as $key => $value) {
            // Only update settings that were actually submitted in this request
            if (!$request->has($key)) {
                continue;
            }

            $oldValue = $allPrevious[$key] ?? null;
            if ($oldValue !== $value) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $value,
                ];
                Setting::set($key, $value);
            }
        }

        // Clear cached settings
        Setting::clearCache();

        // Audit Log
        if (!empty($changes)) {
            AuditLogService::log(
                'settings_updated',
                "Updated site settings (" . implode(', ', array_keys($changes)) . ")",
                null,
                ['changes_count' => count($changes), 'keys' => array_keys($changes)]
            );
        }

        $activeTab = $request->input('active_tab', 'general');

        return redirect()->route('admin.settings.index', ['tab' => $activeTab])
            ->with('success', 'Settings saved successfully.');
    }

    /**
     * Upload branding asset (logo, favicon, etc.).
     */
    public function uploadBranding(Request $request)
    {
        $request->validate([
            'type' => 'required|in:site_logo,site_logo_dark,site_logo_light,site_favicon,apple_touch_icon,default_og_image',
            'file' => 'required|file|mimes:png,jpg,jpeg,svg,ico,webp|max:3072', // max 3MB
        ]);

        $type = $request->type;
        $file = $request->file('file');

        // Store in public disk under branding/
        $extension = $file->getClientOriginalExtension();
        $filename = $type . '_' . time() . '.' . $extension;
        $path = $file->storeAs('branding', $filename, 'public');

        $publicUrl = '/storage/' . $path;

        // Old file removal if existing
        $oldValue = Setting::get($type);
        if ($oldValue && str_starts_with($oldValue, '/storage/branding/')) {
            $oldFilePath = str_replace('/storage/', '', $oldValue);
            if (Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }
        }

        Setting::set($type, $publicUrl);
        Setting::clearCache();

        AuditLogService::log(
            'branding_updated',
            "Uploaded new branding asset: {$type}",
            null,
            ['type' => $type, 'path' => $publicUrl]
        );

        return response()->json([
            'success' => true,
            'url' => $publicUrl,
            'message' => 'Asset uploaded and updated successfully.',
        ]);
    }

    /**
     * Remove branding asset.
     */
    public function removeBranding(Request $request)
    {
        $request->validate([
            'type' => 'required|in:site_logo,site_logo_dark,site_logo_light,site_favicon,apple_touch_icon,default_og_image',
        ]);

        $type = $request->type;
        $oldValue = Setting::get($type);

        if ($oldValue && str_starts_with($oldValue, '/storage/branding/')) {
            $oldFilePath = str_replace('/storage/', '', $oldValue);
            if (Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }
        }

        Setting::set($type, null);
        Setting::clearCache();

        AuditLogService::log(
            'branding_removed',
            "Removed branding asset: {$type}",
            null,
            ['type' => $type]
        );

        return response()->json([
            'success' => true,
            'message' => 'Asset removed successfully.',
        ]);
    }

    /**
     * Clear application caches safely with confirmation.
     */
    public function clearCache(Request $request)
    {
        $request->validate([
            'cache_type' => 'required|in:application,view,route,config,all',
        ]);

        $type = $request->cache_type;
        $messages = [];

        try {
            switch ($type) {
                case 'application':
                    Artisan::call('cache:clear');
                    $messages[] = 'Application data cache cleared.';
                    break;

                case 'view':
                    Artisan::call('view:clear');
                    $messages[] = 'Compiled Blade templates cache cleared.';
                    break;

                case 'route':
                    Artisan::call('route:clear');
                    $messages[] = 'Route cache cleared.';
                    break;

                case 'config':
                    Artisan::call('config:clear');
                    $messages[] = 'Configuration cache cleared.';
                    break;

                case 'all':
                    Artisan::call('cache:clear');
                    Artisan::call('view:clear');
                    Artisan::call('route:clear');
                    Artisan::call('config:clear');
                    $messages[] = 'All application, view, route, and config caches successfully cleared.';
                    break;
            }

            Setting::clearCache();

            AuditLogService::log(
                'cache_cleared',
                "Administrator cleared {$type} cache.",
                null,
                ['cache_type' => $type]
            );

            return back()->with('success', implode(' ', $messages));
        } catch (\Throwable $e) {
            Log::error('Settings cache clear error: ' . $e->getMessage());
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }
}
