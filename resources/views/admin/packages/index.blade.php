@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Manage Vendor Bundles</h1>
        <a href="{{ route('admin.packages.create') }}" class="bg-navy hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
            Create New Bundle
        </a>
    </div>

    <!-- Packages Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-150">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Name / Vendor</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pricing</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Features</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-150 text-xs">
                @forelse($packages as $package)
                    <tr>
                        <td class="px-6 py-4 text-gray-500 font-bold">
                            {{ $package->sort_order }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-navy flex items-center space-x-2">
                                <span>{{ $package->name }}</span>
                                @if($package->is_popular)
                                    <span class="bg-orange text-white text-[9px] px-1.5 py-0.5 rounded-full uppercase tracking-wider">Popular</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-gray-400 font-bold tracking-wider mt-1">{{ $package->vendor ? $package->vendor->name : 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 font-semibold space-y-1">
                            <div><span class="text-gray-400 text-[10px]">Lifetime:</span> ${{ number_format($package->price_lifetime, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ count($package->features ?? []) }} Features
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $package->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $package->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold space-x-2">
                            <a href="{{ route('admin.packages.edit', $package->id) }}" class="text-cyan hover:underline">Edit</a>
                            <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this vendor bundle?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No vendor bundles found. <a href="{{ route('admin.packages.create') }}" class="text-cyan hover:underline font-bold">Create one</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
