@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <div class="flex items-center space-x-2 text-xs text-gray-400 font-semibold mb-1">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-navy transition">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.reviews.index') }}" class="hover:text-navy transition">Reviews</a>
        <span>/</span>
        <span class="text-cyan font-bold">Add Review</span>
    </div>
    <h1 class="text-2xl font-extrabold text-navy tracking-tight">Add Verified Customer Review</h1>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <form action="{{ route('admin.reviews.store') }}" method="POST">
        @csrf
        @include('admin.reviews._form')

        <div class="px-6 sm:px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <a href="{{ route('admin.reviews.index') }}" class="bg-white py-2.5 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 mr-3">
                Cancel
            </a>
            <button type="submit" class="bg-navy hover:bg-opacity-90 border border-transparent rounded-lg shadow-sm py-2.5 px-5 text-sm font-bold text-white">
                Save Review
            </button>
        </div>
    </form>
</div>
@endsection
