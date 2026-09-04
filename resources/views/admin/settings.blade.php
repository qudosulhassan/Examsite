@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">General Site Settings</h1>
        <span class="text-sm text-gray-500">Configure global website options</span>
    </div>

    @if ($errors->any())
        <div class="bg-red-150 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
            <div class="font-bold">Please correct the following errors:</div>
            <ul class="list-disc list-inside text-xs mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg border border-gray-250 p-6 shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Site Brand Config -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-navy border-b border-gray-200 pb-2">Global Brand Identity</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="site_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Site Name</label>
                        <input type="text" name="site_name" id="site_name" value="{{ old('site_name', $settings['site_name'] ?? 'Exam Topics Base') }}" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                    </div>
                    <div>
                        <label for="site_tagline" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Site Tagline</label>
                        <input type="text" name="site_tagline" id="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                    </div>
                    <div>
                        <label for="contact_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Contact Email Address</label>
                        <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? 'support@examtopicsbase.com') }}" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                    </div>
                    <div>
                        <label for="support_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Support Email Address</label>
                        <input type="email" name="support_email" id="support_email" value="{{ old('support_email', $settings['support_email'] ?? 'support@examtopicsbase.com') }}" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="maintenance_mode" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Maintenance Mode</label>
                        <select name="maintenance_mode" id="maintenance_mode" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                            <option value="false" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? 'false') === 'false' ? 'selected' : '' }}>Disabled (Site Online)</option>
                            <option value="true" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? 'false') === 'true' ? 'selected' : '' }}>Enabled (Site Offline)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Subscription Plans JSON -->
            <div class="space-y-4 pt-6 border-t border-gray-150">
                <h3 class="text-sm font-bold text-navy border-b border-gray-200 pb-2">Subscription Plans (JSON Config)</h3>
                <div>
                    <label for="subscription_plans" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Plans Array</label>
                    <textarea name="subscription_plans" id="subscription_plans" rows="8" class="w-full text-xs font-mono border-gray-300 rounded focus:border-cyan focus:ring-cyan">{{ old('subscription_plans', $settings['subscription_plans'] ?? '') }}</textarea>
                    <p class="text-[10px] text-gray-400 mt-1 font-semibold">Must be a valid JSON array matching the plan structure (name, price_monthly, price_annual, features list).</p>
                </div>
            </div>

            <!-- Home Promo Banner Config -->
            <div class="space-y-4 pt-6 border-t border-gray-150">
                <h3 class="text-sm font-bold text-navy border-b border-gray-200 pb-2">Header Promotion Banner</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label for="home_banner_text" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Banner Promotional Message</label>
                        <input type="text" name="home_banner_text" id="home_banner_text" value="{{ old('home_banner_text', $settings['home_banner_text'] ?? '') }}" placeholder="e.g. Get 20% off all certification simulator bundles today!" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                    </div>
                    <div>
                        <label for="home_banner_active" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Banner Visibility Status</label>
                        <select name="home_banner_active" id="home_banner_active" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                            <option value="0" {{ old('home_banner_active', $settings['home_banner_active'] ?? '0') === '0' ? 'selected' : '' }}>Inactive / Hidden</option>
                            <option value="1" {{ old('home_banner_active', $settings['home_banner_active'] ?? '0') === '1' ? 'selected' : '' }}>Active / Visible</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="home_banner_link" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Banner Target Action URL</label>
                    <input type="text" name="home_banner_link" id="home_banner_link" value="{{ old('home_banner_link', $settings['home_banner_link'] ?? '#') }}" placeholder="e.g. /pricing" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                </div>
            </div>

            <!-- Save Action Button -->
            <div class="flex justify-end pt-4 font-bold">
                <button type="submit" class="bg-navy hover:bg-opacity-95 text-white text-sm font-bold py-2 px-6 rounded shadow transition">
                    Save Site Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
