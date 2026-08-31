<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Certification;
use App\Models\Vendor;
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
            'slug' => 'required|string|max:255|unique:certifications',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['slug'] = Str::slug($request->slug);

        $cert = Certification::create($data);

        if ($request->wantsJson()) {
            // Include vendor name for the UI
            $cert->load('vendor');
            return response()->json([
                'success' => true,
                'certification' => $cert,
                'message' => 'Certification created successfully.'
            ]);
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
