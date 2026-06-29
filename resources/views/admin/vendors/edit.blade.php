@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Edit Vendor: {{ $vendor->name }}</h1>
        <a href="{{ route('admin.vendors.index') }}" class="text-sm text-navy hover:underline">
            ← Back to Listing
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm">
        <form action="{{ route('admin.vendors.update', $vendor->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Vendor Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-gray-400 uppercase mb-2">Vendor Name</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $vendor->name) }}"
                       class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>



            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-xs font-bold text-gray-400 uppercase mb-2">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" required value="{{ old('sort_order', $vendor->sort_order) }}"
                       class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                @error('sort_order')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold text-gray-400 uppercase mb-2">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">{{ old('description', $vendor->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Is Active Checkbox -->
            <div class="flex items-center mb-8">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $vendor->is_active) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                <label for="is_active" class="ml-2 text-sm font-semibold text-gray-700">Mark as Active (Show in search and pricing lists)</label>
            </div>

            <!-- SEO Configuration Card -->
            <div class="bg-gray-50 -mx-6 px-6 py-6 border-t border-b border-gray-150 mb-8">
                <h3 class="text-sm font-extrabold text-navy uppercase mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Search Engine Optimization (SEO)
                </h3>
                <p class="text-xs text-gray-500 mb-6">Leave these blank to automatically generate them based on the vendor name and description.</p>
                
                <div class="space-y-4">
                    <div>
                        <label for="meta_title" class="block text-xs font-bold text-gray-400 uppercase mb-2">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $vendor->meta_title) }}" placeholder="e.g., Best Cisco Certification Study Guides"
                               class="w-full bg-white border border-gray-200 rounded px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-cyan focus:ring-1 focus:ring-cyan">
                        @error('meta_title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="meta_description" class="block text-xs font-bold text-gray-400 uppercase mb-2">Meta Description (Max 160 chars)</label>
                        <textarea name="meta_description" id="meta_description" rows="2" placeholder="Write a compelling description for Google search results..."
                                  class="w-full bg-white border border-gray-200 rounded px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-cyan focus:ring-1 focus:ring-cyan">{{ old('meta_description', $vendor->meta_description) }}</textarea>
                        @error('meta_description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="meta_keywords" class="block text-xs font-bold text-gray-400 uppercase mb-2">Meta Keywords (Comma-separated)</label>
                        <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords', $vendor->meta_keywords) }}" placeholder="e.g., cisco exams, ccna dumps, cisco practice tests"
                               class="w-full bg-white border border-gray-200 rounded px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-cyan focus:ring-1 focus:ring-cyan">
                        @error('meta_keywords') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="border-t border-gray-150 pt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.vendors.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-6 rounded transition">
                    Cancel
                </a>
                <button type="submit" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-6 rounded shadow transition">
                    Update Vendor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
