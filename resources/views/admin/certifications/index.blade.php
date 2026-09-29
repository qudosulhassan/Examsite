@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-400 font-semibold mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-navy transition">Dashboard</a>
                <span>/</span>
                <span class="text-cyan font-bold">Certifications</span>
            </div>
            <h1 class="text-2xl font-extrabold text-navy tracking-tight">Certification Management</h1>
            <p class="text-xs text-gray-500 mt-0.5">Manage certification titles, vendor assignments, and SEO metadata.</p>
        </div>

        <a href="{{ route('admin.certifications.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-orange hover:bg-opacity-90 text-white text-xs font-black rounded-xl shadow-md shadow-orange/20 transition-all transform hover:-translate-y-0.5">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Certification
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total</span>
                <div class="w-8 h-8 rounded-xl bg-navy/5 text-navy flex items-center justify-center font-bold text-sm">🎓</div>
            </div>
            <div class="text-2xl font-black text-navy">{{ number_format($totalCertifications) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Certifications</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Active</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">✓</div>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ number_format($activeCertifications) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">{{ $totalCertifications > 0 ? round(($activeCertifications/$totalCertifications)*100, 1) : 0 }}% of total</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Inactive</span>
                <div class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-sm">⛔</div>
            </div>
            <div class="text-2xl font-black text-red-600">{{ number_format($inactiveCertifications) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Hidden from public site</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Vendors</span>
                <div class="w-8 h-8 rounded-xl bg-cyan/10 text-cyan flex items-center justify-center font-bold text-sm">🏷️</div>
            </div>
            <div class="text-2xl font-black text-navy">{{ number_format($vendorsCovered) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Vendors covered</div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-5">
        <form action="{{ route('admin.certifications.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1" style="min-width: 260px;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, slug, code, or vendor..."
                           class="w-full h-[42px] pl-10 pr-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-navy placeholder-gray-400 focus:bg-white focus:border-cyan focus:ring-2 focus:ring-cyan/20 outline-none transition-all">
                </div>
            </div>

            <div style="min-width: 180px;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Vendor</label>
                <select name="vendor_id" class="w-full h-[42px] px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-navy focus:bg-white focus:border-cyan focus:ring-2 focus:ring-cyan/20 outline-none transition-all cursor-pointer">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ (string) request('vendor_id') === (string) $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 150px;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="w-full h-[42px] px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-navy focus:bg-white focus:border-cyan focus:ring-2 focus:ring-cyan/20 outline-none transition-all cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="h-[42px] px-5 bg-navy hover:bg-opacity-90 text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Search
                </button>
                @if(request()->anyFilled(['search', 'vendor_id', 'status']))
                    <a href="{{ route('admin.certifications.index') }}" class="h-[42px] w-[42px] flex items-center justify-center bg-gray-100 hover:bg-red-50 hover:text-red-600 text-gray-500 rounded-xl border border-gray-200 transition" title="Reset filters">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Certifications Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-150">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Certification</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Exams</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sort Order</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-150 text-xs">
                    @forelse($certifications as $cert)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center shrink-0 text-gray-400 font-bold text-[10px]">
                                    {{ strtoupper(substr($cert->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-navy">{{ $cert->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $cert->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-navy/5 text-navy border border-navy/10">
                                {{ $cert->vendor->name ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-cyan/10 text-cyan border border-cyan/20">
                                {{ $cert->exams_count }} {{ Str::plural('exam', $cert->exams_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-semibold">
                            {{ $cert->sort_order }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $cert->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $cert->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 font-bold">
                            <a href="{{ route('admin.certifications.edit', $cert->id) }}" class="text-cyan hover:underline">Edit</a>
                            <form action="{{ route('admin.certifications.destroy', $cert->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this certification?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span class="font-semibold">No certifications found.</span>
                                @if(request()->anyFilled(['search', 'vendor_id', 'status']))
                                    <p class="text-[11px] text-gray-400">Try adjusting your search criteria or resetting filters.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($certifications->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
            {{ $certifications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
