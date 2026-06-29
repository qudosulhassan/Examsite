@extends('layouts.admin')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.packages.index') }}" class="text-gray-400 hover:text-navy transition">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Create New Package</h1>
    </div>

    <form action="{{ route('admin.packages.store') }}" method="POST">
        @csrf
        @include('admin.packages._form')
    </form>
</div>
@endsection
