<div class="flex flex-col lg:flex-row gap-6 items-start" x-data="{ 
    features: {{ json_encode(old('features', $package->features ?? [''])) }},
    addFeature() { this.features.push(''); },
    removeFeature(index) { this.features.splice(index, 1); if (this.features.length === 0) this.addFeature(); },
    moveFeatureUp(index) { if (index > 0) { let temp = this.features[index]; this.features[index] = this.features[index-1]; this.features[index-1] = temp; } },
    moveFeatureDown(index) { if (index < this.features.length - 1) { let temp = this.features[index]; this.features[index] = this.features[index+1]; this.features[index+1] = temp; } }
}">
    
    <!-- Main Content Column -->
    <div class="flex-1 space-y-6 w-full lg:max-w-[70%]">
        
        <!-- Basic Information -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Basic Information
                </h3>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Select Vendor</label>
                    <select name="vendor_id" class="w-full border-gray-250 rounded-lg shadow-sm focus:ring-cyan focus:border-cyan text-sm" required>
                        <option value="">-- Choose Vendor --</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id', $package->vendor_id ?? '') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('vendor_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Bundle Name</label>
                    <input type="text" name="name" value="{{ old('name', $package->name ?? '') }}" placeholder="e.g., Ultimate Pro" class="w-full border-gray-250 rounded-lg shadow-sm focus:ring-cyan focus:border-cyan text-sm placeholder-gray-300" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <textarea name="description" rows="2" placeholder="Brief tagline or description..." class="w-full border-gray-250 rounded-lg shadow-sm focus:ring-cyan focus:border-cyan text-sm placeholder-gray-300">{{ old('description', $package->description ?? '') }}</textarea>
                    @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Pricing Configuration -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Pricing
                </h3>
            </div>
            <div class="p-6">
                <!-- Bundle Pricing -->
                <div class="grid grid-cols-1 gap-5 max-w-sm">
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Lifetime Price</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-400 sm:text-sm">$</span>
                            </div>
                            <input type="number" step="0.01" name="price_lifetime" value="{{ old('price_lifetime', $package->price_lifetime ?? '') }}" placeholder="0.00" class="w-full border-gray-250 rounded-lg pl-7 focus:ring-cyan focus:border-cyan text-sm">
                        </div>
                        @error('price_lifetime')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Pricing Options (Update Period & License Type) -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    Additional Options Pricing
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Update Period Prices -->
                <div>
                    <h4 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">Update Period Extra Cost</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="relative">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">3 Months (+)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-400 sm:text-sm">$</span></div>
                                <input type="number" step="0.01" name="update_price_3_months" value="{{ old('update_price_3_months', $package->update_price_3_months ?? '') }}" class="w-full border-gray-250 rounded-lg pl-7 focus:ring-cyan focus:border-cyan text-sm">
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">6 Months (+)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-400 sm:text-sm">$</span></div>
                                <input type="number" step="0.01" name="update_price_6_months" value="{{ old('update_price_6_months', $package->update_price_6_months ?? '') }}" class="w-full border-gray-250 rounded-lg pl-7 focus:ring-cyan focus:border-cyan text-sm">
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">12 Months (+)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-400 sm:text-sm">$</span></div>
                                <input type="number" step="0.01" name="update_price_12_months" value="{{ old('update_price_12_months', $package->update_price_12_months ?? '') }}" class="w-full border-gray-250 rounded-lg pl-7 focus:ring-cyan focus:border-cyan text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- License Type Prices -->
                <div>
                    <h4 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">License Type Extra Cost</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="relative">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Individual 2 PCs (+)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-400 sm:text-sm">$</span></div>
                                <input type="number" step="0.01" name="license_price_individual" value="{{ old('license_price_individual', $package->license_price_individual ?? '') }}" class="w-full border-gray-250 rounded-lg pl-7 focus:ring-cyan focus:border-cyan text-sm">
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Corporate 10 PCs (+)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-400 sm:text-sm">$</span></div>
                                <input type="number" step="0.01" name="license_price_corporate" value="{{ old('license_price_corporate', $package->license_price_corporate ?? '') }}" class="w-full border-gray-250 rounded-lg pl-7 focus:ring-cyan focus:border-cyan text-sm">
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Trainer 25 PCs (+)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-400 sm:text-sm">$</span></div>
                                <input type="number" step="0.01" name="license_price_trainer" value="{{ old('license_price_trainer', $package->license_price_trainer ?? '') }}" class="w-full border-gray-250 rounded-lg pl-7 focus:ring-cyan focus:border-cyan text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Builder -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="text-base font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Features Included
                </h3>
                <button type="button" @click="addFeature" class="text-sm font-bold text-cyan hover:text-navy transition flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add Item</span>
                </button>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <template x-for="(feature, index) in features" :key="index">
                        <div class="flex items-center space-x-3 group bg-white border border-gray-200 rounded-lg p-2 transition-shadow hover:shadow-sm">
                            <div class="flex flex-col text-gray-300">
                                <button type="button" @click="moveFeatureUp(index)" class="hover:text-cyan p-0.5" :disabled="index === 0" :class="{ 'opacity-30 cursor-not-allowed': index === 0 }">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                </button>
                                <button type="button" @click="moveFeatureDown(index)" class="hover:text-cyan p-0.5" :disabled="index === features.length - 1" :class="{ 'opacity-30 cursor-not-allowed': index === features.length - 1 }">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                            </div>
                            <input type="text" x-model="features[index]" :name="'features[' + index + ']'" placeholder="e.g. Full PDF Downloads" class="flex-1 border-0 bg-transparent focus:ring-0 text-sm font-medium text-gray-700 placeholder-gray-400 py-1.5">
                            <button type="button" @click="removeFeature(index)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-md transition opacity-50 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Settings Column -->
    <div class="w-full lg:w-80 space-y-6">
        
        <!-- Status & Visibility -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Settings</h3>
            </div>
            <div class="p-5 space-y-5">
                
                <!-- Sort Order -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Display Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $package->sort_order ?? 0) }}" class="w-full border-gray-250 rounded-lg shadow-sm focus:ring-cyan focus:border-cyan text-sm" required>
                    <p class="text-[11px] text-gray-400 mt-1">Lower numbers appear first (e.g. 1, 2, 3).</p>
                    @error('sort_order')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <hr class="border-gray-150">

                <!-- Capabilities -->
                <div class="space-y-4">
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-bold text-gray-700 group-hover:text-navy transition">Includes PDF</span>
                        <div class="relative">
                            <input type="hidden" name="includes_pdf" value="0">
                            <input type="checkbox" name="includes_pdf" value="1" class="sr-only peer" {{ old('includes_pdf', $package->includes_pdf ?? true) ? 'checked' : '' }}>
                            <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-cyan"></div>
                        </div>
                    </label>

                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-bold text-gray-700 group-hover:text-navy transition">Includes Test Engine</span>
                        <div class="relative">
                            <input type="hidden" name="includes_te" value="0">
                            <input type="checkbox" name="includes_te" value="1" class="sr-only peer" {{ old('includes_te', $package->includes_te ?? false) ? 'checked' : '' }}>
                            <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-cyan"></div>
                        </div>
                    </label>
                </div>

                <hr class="border-gray-150">

                <!-- Toggles -->
                <div class="space-y-4">
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-bold text-gray-700 group-hover:text-navy transition">Active / Published</span>
                        <div class="relative">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
                            <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                        </div>
                    </label>

                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-bold text-gray-700 group-hover:text-navy transition">Highlight as Popular</span>
                        <div class="relative">
                            <input type="hidden" name="is_popular" value="0">
                            <input type="checkbox" name="is_popular" value="1" class="sr-only peer" {{ old('is_popular', $package->is_popular ?? false) ? 'checked' : '' }}>
                            <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-orange"></div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <button type="submit" class="w-full bg-cyan hover:bg-navy text-white px-6 py-3.5 rounded-xl text-sm font-bold transition shadow-lg shadow-cyan/20 flex justify-center items-center space-x-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
            <span>Save Package</span>
        </button>
    </div>
</div>
