@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Edit Certification: {{ $certification->name }}</h1>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <form action="{{ route('admin.certifications.update', $certification->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.certifications._form')
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <a href="{{ route('admin.certifications.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                Cancel
            </a>
            <button type="submit" class="bg-indigo-600 border border-transparent rounded-md shadow-sm py-2 px-4 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update Certification
            </button>
        </div>
    </form>
</div>
@endsection
