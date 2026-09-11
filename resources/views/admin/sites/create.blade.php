@extends('layouts.admin')

@section('title', 'Add New Website')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Website</h1>
            <p class="mt-1 text-sm text-gray-500">Connect a new domain to share the central question and exam bank with independent branding.</p>
        </div>
        <div>
            <a href="{{ route('admin.sites.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">
                &larr; Back to Websites
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.sites.store') }}" method="POST" class="bg-white shadow-sm border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Website Brand Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. CertPass Master" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Unique Code Identifier</label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. certmaster" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Primary Domain (Hostname)</label>
                <input type="text" name="domain" value="{{ old('domain') }}" placeholder="e.g. certpassmaster.com or certmaster.test" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">
                <p class="text-xs text-gray-400 mt-1">Point this domain to your server's IP address in your DNS manager.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Frontend Theme</label>
                <select name="default_theme" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                    <option value="default">Default Theme (Standard)</option>
                    <option value="modern">Modern Theme</option>
                    <option value="minimal">Minimal Theme</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Default Locale</label>
                <input type="text" name="default_locale" value="en" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">
            </div>

            <div class="sm:col-span-2 flex items-center">
                <input type="checkbox" id="is_active" name="is_active" value="1" checked class="h-4 w-4 text-cyan focus:ring-cyan border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm font-medium text-gray-900">Website is active and publicly accessible</label>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.sites.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-cyan hover:bg-cyan/90 transition">Save Website</button>
        </div>
    </form>
</div>
@endsection
