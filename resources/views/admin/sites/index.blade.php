@extends('layouts.admin')

@section('title', 'Websites & Multi-Tenancy Platform')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Websites Platform</h1>
            <p class="mt-1 text-sm text-gray-500">Manage independent frontend websites running on your shared core database.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.sites.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-cyan hover:bg-cyan/90 transition">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add New Website
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Sites Table -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-2xl overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Website</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Primary Domain</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Theme</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Published Exams</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Vendors</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">SEO Health</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="relative px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @foreach($sites as $site)
                @php
                    $metrics = $site->metrics;
                    $primaryDomain = $site->primaryDomain ? $site->primaryDomain->domain : ($site->domains->first()->domain ?? 'localhost');
                    $seoScore = $site->seo_health;
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-navy/5 text-navy font-black flex items-center justify-center text-xs border border-gray-200">
                                {{ strtoupper(substr($site->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-bold text-navy text-base leading-tight">{{ $site->name }}</div>
                                <div class="text-[11px] text-gray-400 font-mono">Code: {{ $site->code }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        <div class="font-mono text-xs font-bold text-gray-800 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-cyan"></span>
                            <a href="http://{{ $primaryDomain }}:8000" target="_blank" class="hover:text-cyan hover:underline flex items-center gap-1">
                                <span>{{ $primaryDomain }}</span>
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                        @if($site->domains->count() > 1)
                            <div class="text-[10px] text-gray-400 mt-0.5">+{{ $site->domains->count() - 1 }} alias domain(s)</div>
                        @endif
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-700">
                            {{ ucfirst($site->default_theme) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap font-medium text-gray-700">
                        <span class="font-bold text-navy">{{ $metrics['published_exams'] }}</span> exams
                        <span class="text-[11px] text-gray-400 block">({{ $metrics['total_questions'] }} questions)</span>
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap font-medium text-gray-700">
                        <span class="font-bold text-navy">{{ $metrics['available_vendors'] }}</span> vendors
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="bg-cyan h-2 rounded-full" style="width: {{ $seoScore }}%"></div>
                            </div>
                            <span class="text-xs font-bold text-navy">{{ $seoScore }}%</span>
                        </div>
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        @if($site->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap text-right text-xs font-medium space-x-2">
                        <a href="{{ route('admin.sites.edit', ['site' => $site->id, 'tab' => 'general']) }}" class="text-cyan hover:text-cyan-700 font-bold">Edit</a>
                        <a href="{{ route('admin.sites.exams', $site->id) }}" class="text-navy hover:text-cyan font-bold">Manage Exams</a>
                        <a href="{{ route('admin.sites.edit', ['site' => $site->id, 'tab' => 'seo']) }}" class="text-gray-600 hover:text-navy font-bold">Manage SEO</a>
                        <a href="{{ route('admin.sites.edit', ['site' => $site->id, 'tab' => 'theme']) }}" class="text-gray-600 hover:text-navy font-bold">Manage Theme</a>
                        <a href="http://{{ $primaryDomain }}:8000" target="_blank" class="text-gray-400 hover:text-gray-700 font-bold">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
