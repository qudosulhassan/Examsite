@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-navy dark:text-white">My Purchased Guides</h1>
            <p class="text-sm text-gray-500">Download printable PDF study guides and dumps containing verified questions and explanations.</p>
        </div>
    </div>

    @if(count($purchasedExams) > 0)
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-750 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">Exam Code</th>
                            <th scope="col" class="px-6 py-4">Exam Name</th>
                            <th scope="col" class="px-6 py-4">Vendor</th>
                            <th scope="col" class="px-6 py-4">Purchased</th>
                            <th scope="col" class="px-6 py-4">Downloads</th>
                            <th scope="col" class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-navy dark:text-gray-200">
                        @foreach($purchasedExams as $userExam)
                            @if($userExam->exam)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-cyan">{{ $userExam->exam->exam_code }}</td>
                                    <td class="px-6 py-4 font-semibold">{{ $userExam->exam->exam_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $userExam->exam->vendor ? $userExam->exam->vendor->name : 'IT Provider' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        {{ $userExam->purchased_at ? $userExam->purchased_at->format('M d, Y') : $userExam->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-bold text-navy dark:text-white">{{ $userExam->download_count }}</span> / {{ $userExam->max_downloads }} Used
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($userExam->canDownload())
                                            <a href="{{ route('dashboard.my-exams.download', $userExam->id) }}" class="inline-flex items-center space-x-1 bg-orange hover:bg-opacity-95 text-white text-xs font-bold py-2 px-4 rounded shadow transition">
                                                <span>Download PDF</span>
                                            </a>
                                        @else
                                            <span class="inline-block bg-gray-200 text-gray-500 text-xs font-bold py-2 px-4 rounded cursor-not-allowed">
                                                Limit Reached
                                            </span>
                                            <span class="block text-[10px] text-gray-400 mt-1"><a href="{{ url('/contact') }}" class="text-cyan hover:underline">Request Reset</a></span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-16 text-center text-gray-500">
            <p class="text-lg font-semibold mb-2">No purchased exam guides found.</p>
            <p class="text-sm text-gray-400 mb-6 max-w-sm mx-auto">Purchase any printable PDF guide to unlock instant lifetime access to verified answers.</p>
            <a href="{{ url('/vendors') }}" class="bg-navy hover:bg-opacity-95 text-white font-bold text-xs px-6 py-3 rounded shadow transition">
                Browse Certification Exams
            </a>
        </div>
    @endif
</div>
@endsection
