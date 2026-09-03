<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VendorAdminController extends Controller
{
    public function index()
    {
        $vendors = Vendor::orderBy('sort_order')->paginate(10);
        return view('admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'description' => 'nullable|string',
            'sort_order' => 'required|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('vendors', 'public');
            $logoPath = '/storage/' . $path;
        }

        Vendor::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'logo_path' => $logoPath,
            'description' => $request->description,
            'sort_order' => $request->sort_order,
            'is_active' => $request->has('is_active'),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ]);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function edit(int $id)
    {
        $vendor = Vendor::findOrFail($id);
        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, int $id)
    {
        $vendor = Vendor::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'description' => 'nullable|string',
            'sort_order' => 'required|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => $request->sort_order,
            'is_active' => $request->has('is_active'),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ];

        if ($request->boolean('remove_logo')) {
            if ($vendor->logo_path && str_starts_with($vendor->logo_path, '/storage/')) {
                $storagePath = str_replace('/storage/', '', $vendor->logo_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($storagePath);
            }
            $data['logo_path'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($vendor->logo_path && str_starts_with($vendor->logo_path, '/storage/')) {
                $storagePath = str_replace('/storage/', '', $vendor->logo_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($storagePath);
            }
            $path = $request->file('logo')->store('vendors', 'public');
            $data['logo_path'] = '/storage/' . $path;
        }

        $vendor->update($data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(int $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}
