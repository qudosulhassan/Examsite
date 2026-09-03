@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Manage Certification Vendors</h1>
        <a href="{{ route('admin.vendors.create') }}" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2 px-4 rounded shadow transition">
            + Add New Vendor
        </a>
    </div>

    <!-- Vendors Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-150">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Vendor Name</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sort Order</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-150 text-xs">
                @forelse($vendors as $vendor)
                    <tr>
                        <td class="px-6 py-4 font-bold text-navy flex items-center space-x-3">
                            @if($vendor->logo_url)
                                <div class="w-8 h-8 rounded border border-gray-200 bg-white p-1 flex items-center justify-center shrink-0 shadow-xs">
                                    <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->name }}" class="max-h-full max-w-full object-contain">
                                </div>
                            @else
                                <div class="w-8 h-8 rounded border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center shrink-0 text-gray-400 font-bold text-[10px]">
                                    {{ strtoupper(substr($vendor->name, 0, 2)) }}
                                </div>
                            @endif
                            <span>{{ $vendor->name }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-semibold">
                            {{ $vendor->sort_order }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $vendor->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 font-bold">
                            <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="text-cyan hover:underline">Edit</a>
                            <form action="{{ route('admin.vendors.destroy', $vendor->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this vendor? This will delete all associated exams.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            No certification vendors found in database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($vendors->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
