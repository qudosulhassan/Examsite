@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Create New Coupon</h1>
        <a href="{{ route('admin.coupons.index') }}" class="text-sm text-navy hover:underline">
            ← Back to Listing
        </a>
    </div>

    <!-- Create Form -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm">
        <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Code -->
                <div>
                    <label for="code" class="block text-xs font-bold text-gray-400 uppercase mb-2">Coupon Code</label>
                    <input type="text" name="code" id="code" required placeholder="e.g. NINJA50"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 uppercase focus:border-cyan focus:ring-cyan">
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Applicable To -->
                <div>
                    <label for="applicable_to" class="block text-xs font-bold text-gray-400 uppercase mb-2">Applicable To</label>
                    <select name="applicable_to" id="applicable_to" required
                            class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                        <option value="all">All Packages</option>
                        <option value="pdf_only">PDF Guides Only</option>
                        <option value="subscription_only">Subscriptions Only</option>
                    </select>
                    @error('applicable_to')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Discount Type -->
                <div>
                    <label for="discount_type" class="block text-xs font-bold text-gray-400 uppercase mb-2">Discount Type</label>
                    <select name="discount_type" id="discount_type" required
                            class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Flat Rate ($)</option>
                    </select>
                    @error('discount_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Discount Value -->
                <div>
                    <label for="discount_value" class="block text-xs font-bold text-gray-400 uppercase mb-2">Discount Value</label>
                    <input type="number" step="0.01" name="discount_value" id="discount_value" required placeholder="e.g. 10"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('discount_value')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Min Order Amount -->
                <div>
                    <label for="min_order_amount" class="block text-xs font-bold text-gray-400 uppercase mb-2">Min Spend ($)</label>
                    <input type="number" step="0.01" name="min_order_amount" id="min_order_amount" required value="0"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('min_order_amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Max Uses -->
                <div>
                    <label for="max_uses" class="block text-xs font-bold text-gray-400 uppercase mb-2">Max Uses (Total)</label>
                    <input type="number" name="max_uses" id="max_uses" placeholder="e.g. 100 (Optional)"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('max_uses')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Per User Limit -->
                <div>
                    <label for="per_user_limit" class="block text-xs font-bold text-gray-400 uppercase mb-2">Per User Limit</label>
                    <input type="number" name="per_user_limit" id="per_user_limit" required value="1"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('per_user_limit')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Expiry Date -->
                <div>
                    <label for="expires_at" class="block text-xs font-bold text-gray-400 uppercase mb-2">Expiry Date</label>
                    <input type="date" name="expires_at" id="expires_at"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('expires_at')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-xs font-bold text-gray-400 uppercase mb-2">Description</label>
                    <input type="text" name="description" id="description" placeholder="Coupon context description..."
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Is Active Checkbox -->
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" checked value="1"
                       class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                <label for="is_active" class="ml-2 text-sm font-semibold text-gray-700">Mark as Active (Available for use)</label>
            </div>

            <!-- Submit Buttons -->
            <div class="border-t border-gray-150 pt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.coupons.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-6 rounded transition">
                    Cancel
                </a>
                <button type="submit" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-6 rounded shadow transition">
                    Save Coupon
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
