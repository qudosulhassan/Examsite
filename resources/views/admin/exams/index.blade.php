@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Manage Certification Exams</h1>
        <a href="{{ route('admin.exams.create') }}" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2 px-4 rounded shadow transition">
            + Create New Exam
        </a>
    </div>

    <!-- Exams Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-150">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Exam Code / Vendor</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Exam Name</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">PDF Price</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Engine Price</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-150 text-xs">
                @forelse($exams as $exam)
                    <tr>
                        <td class="px-6 py-4">
                            <span class="font-extrabold text-navy block">{{ $exam->exam_code }}</span>
                            <span class="text-[10px] text-gray-400 font-semibold">{{ $exam->vendor->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-700">{{ $exam->exam_name }}</div>
                            <div class="text-[10px] text-gray-400">{{ count($exam->topics ?: []) }} Topics | {{ $exam->difficulty }}</div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-700">
                            ${{ number_format($exam->price_pdf, 2) }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-700">
                            ${{ number_format($exam->price_engine, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $exam->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $exam->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 font-bold">
                            <a href="{{ route('admin.exams.edit', $exam->id) }}" class="text-cyan hover:underline">Edit</a>
                            <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this exam? This will delete all associated questions and attempts.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            No certification exams found in database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($exams->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
                {{ $exams->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
