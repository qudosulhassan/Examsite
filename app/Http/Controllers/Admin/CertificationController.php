<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certification;
use App\Models\Vendor;
use App\Services\AuditLogService;
use Illuminate\Support\Str;

class CertificationController extends Controller
{
    public function index()
    {
        $certifications = Certification::with('vendor')->latest()->paginate(15);
        return view('admin.certifications.index', compact('certifications'));
    }

    public function create()
    {
        $vendors = Vendor::orderBy('name')->get();
        return view('admin.certifications.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name);

        // Duplicate protection: Check if similar certification exists under this vendor or matching slug
        $existing = Certification::where('vendor_id', $request->vendor_id)
            ->where(function ($q) use ($request, $slug) {
                $q->whereRaw('LOWER(name) = ?', [strtolower(trim($request->name))])
                  ->orWhere('slug', $slug);
                if ($request->filled('code')) {
                    $q->orWhere('code', trim($request->code));
                }
            })
            ->first();

        if ($existing) {
            if ($request->wantsJson() || $request->ajax()) {
                $existing->load('vendor');
                return response()->json([
                    'success' => false,
                    'is_duplicate' => true,
                    'existing_certification' => [
                        'id' => $existing->id,
                        'name' => $existing->name,
                        'code' => $existing->code,
                        'vendor_id' => $existing->vendor_id,
                        'vendor_name' => $existing->vendor ? $existing->vendor->name : 'Unknown',
                    ],
                    'message' => 'Certification already exists.'
                ], 422);
            }

            return back()->withInput()->withErrors(['name' => 'Certification already exists for this vendor.']);
        }

        $isActive = true;
        if ($request->has('is_active')) {
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        $data = [
            'vendor_id' => $request->vendor_id,
            'name' => trim($request->name),
            'code' => $request->filled('code') ? trim($request->code) : null,
            'slug' => $slug,
            'description' => $request->description,
            'is_active' => $isActive,
            'meta_title' => $request->meta_title ?? trim($request->name),
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'sort_order' => $request->filled('sort_order') ? (int)$request->sort_order : 0,
        ];

        $cert = Certification::create($data);
        $cert->load('vendor');

        AuditLogService::log(
            'certification_created',
            "Created certification: {$cert->name}" . ($cert->vendor ? " ({$cert->vendor->name})" : ''),
            null,
            ['certification_id' => $cert->id, 'vendor_id' => $cert->vendor_id]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'certification' => [
                    'id' => $cert->id,
                    'name' => $cert->name,
                    'code' => $cert->code,
                    'vendor_id' => $cert->vendor_id,
                    'vendor_name' => $cert->vendor ? $cert->vendor->name : 'Unknown',
                ],
                'message' => 'Certification created successfully.'
            ], 201);
        }

        return redirect()->route('admin.certifications.index')->with('success', 'Certification created successfully.');
    }

    public function edit(Certification $certification)
    {
        $vendors = Vendor::orderBy('name')->get();
        return view('admin.certifications.edit', compact('certification', 'vendors'));
    }

    public function update(Request $request, Certification $certification)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:certifications,slug,' . $certification->id,
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['slug'] = Str::slug($request->slug);

        $certification->update($data);

        return redirect()->route('admin.certifications.index')->with('success', 'Certification updated successfully.');
    }

    public function destroy(Certification $certification)
    {
        $certification->delete();
        return redirect()->route('admin.certifications.index')->with('success', 'Certification deleted successfully.');
    }
}
