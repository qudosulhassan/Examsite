<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageAdminController extends Controller
{
    public function index()
    {
        $packages = Package::orderBy('sort_order', 'asc')->get();
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        return view('admin.packages.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_lifetime' => 'nullable|numeric|min:0',
            'update_price_3_months' => 'nullable|numeric|min:0',
            'update_price_6_months' => 'nullable|numeric|min:0',
            'update_price_12_months' => 'nullable|numeric|min:0',
            'license_price_individual' => 'nullable|numeric|min:0',
            'license_price_corporate' => 'nullable|numeric|min:0',
            'license_price_trainer' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'sort_order' => 'required|integer',
        ]);

        $validated['type'] = 'bundle';

        $validated['slug'] = Str::slug($validated['name']) . '-' . rand(100, 999);
        $validated['is_popular'] = $request->has('is_popular');
        $validated['is_active'] = $request->has('is_active');
        $validated['includes_pdf'] = $request->has('includes_pdf');
        $validated['includes_te'] = $request->has('includes_te');
        
        // Clean features array
        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features']));
        } else {
            $validated['features'] = [];
        }

        Package::create($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(Package $package)
    {
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        return view('admin.packages.edit', compact('package', 'vendors'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_lifetime' => 'nullable|numeric|min:0',
            'update_price_3_months' => 'nullable|numeric|min:0',
            'update_price_6_months' => 'nullable|numeric|min:0',
            'update_price_12_months' => 'nullable|numeric|min:0',
            'license_price_individual' => 'nullable|numeric|min:0',
            'license_price_corporate' => 'nullable|numeric|min:0',
            'license_price_trainer' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'sort_order' => 'required|integer',
        ]);

        $validated['type'] = 'bundle';

        $validated['is_popular'] = $request->has('is_popular');
        $validated['is_active'] = $request->has('is_active');
        $validated['includes_pdf'] = $request->has('includes_pdf');
        $validated['includes_te'] = $request->has('includes_te');
        
        // Clean features array
        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features']));
        } else {
            $validated['features'] = [];
        }

        $package->update($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully.');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Package deleted successfully.');
    }
}
