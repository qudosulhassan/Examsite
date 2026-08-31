<div class="p-6 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="vendor_id" class="block text-sm font-medium text-gray-700">Vendor</label>
            <select name="vendor_id" id="vendor_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Select Vendor</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ old('vendor_id', $certification->vendor_id ?? '') == $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->name }}
                    </option>
                @endforeach
            </select>
            @error('vendor_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Certification Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $certification->name ?? '') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g. Cisco Certified Network Associate (CCNA)">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $certification->slug ?? '') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="cisco-certified-network-associate-ccna">
            @error('slug') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $certification->sort_order ?? 0) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <p class="text-xs text-gray-500 mt-1">Lower numbers appear first.</p>
            @error('sort_order') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description', $certification->description ?? '') }}</textarea>
        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div class="flex items-center">
        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $certification->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
        <label for="is_active" class="ml-2 block text-sm text-gray-900">Mark as Active (Show in search and lists)</label>
    </div>

    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mt-6">
        <h3 class="text-sm font-medium text-gray-900 mb-4 uppercase tracking-wider flex items-center">
            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            Search Engine Optimization (SEO)
        </h3>
        <div class="space-y-4">
            <div>
                <label for="meta_title" class="block text-xs font-medium text-gray-700">Meta Title</label>
                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $certification->meta_title ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="meta_description" class="block text-xs font-medium text-gray-700">Meta Description (Max 160 chars)</label>
                <textarea name="meta_description" id="meta_description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('meta_description', $certification->meta_description ?? '') }}</textarea>
            </div>
            <div>
                <label for="meta_keywords" class="block text-xs font-medium text-gray-700">Meta Keywords (Comma-separated)</label>
                <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords', $certification->meta_keywords ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        if (!document.getElementById('slug').dataset.manuallyEdited) {
            let slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
            document.getElementById('slug').value = slug;
        }
    });

    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manuallyEdited = true;
    });
</script>
