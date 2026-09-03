@extends('layouts.public')

@section('title', "Search Results for '" . $query . "' - ExamsNinja")

@section('content')
<!-- Header Banner -->
<section class="bg-navy text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Search Results for: <span class="text-cyan">"{{ $query }}"</span>
        </h1>
        <p class="text-sm text-gray-300 mt-1">Found {{ $exams->total() }} matching certification guides.</p>
    </div>
</section>

<!-- Filter and Results Grid -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Sidebar Filters (Left) -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-5 h-fit space-y-6">
            <h3 class="font-bold text-navy text-sm border-b border-gray-200 pb-2">Filter Results</h3>
            
            <form action="{{ url('/search') }}" method="GET" class="space-y-4">
                <!-- Keep search query -->
                <input type="hidden" name="q" value="{{ $query }}">

                <!-- Difficulty Filter -->
                <div>
                    <label class="block text-xs font-bold text-navy uppercase mb-1.5">Difficulty Level</label>
                    <select name="difficulty" class="w-full rounded bg-white border border-gray-300 text-xs py-2 px-2.5 text-gray-700 focus:outline-none focus:ring-1 focus:ring-cyan">
                        <option value="">All Levels</option>
                        <option value="Associate" {{ request('difficulty') === 'Associate' ? 'selected' : '' }}>Associate</option>
                        <option value="Professional" {{ request('difficulty') === 'Professional' ? 'selected' : '' }}>Professional</option>
                        <option value="Expert" {{ request('difficulty') === 'Expert' ? 'selected' : '' }}>Expert</option>
                    </select>
                </div>

                <!-- Exam Type Filter -->
                <div>
                    <label class="block text-xs font-bold text-navy uppercase mb-1.5">Question Type</label>
                    <select name="type" class="w-full rounded bg-white border border-gray-300 text-xs py-2 px-2.5 text-gray-700 focus:outline-none focus:ring-1 focus:ring-cyan">
                        <option value="">All Types</option>
                        <option value="MultipleChoice" {{ request('type') === 'MultipleChoice' ? 'selected' : '' }}>Multiple Choice</option>
                        <option value="MultiSelect" {{ request('type') === 'MultiSelect' ? 'selected' : '' }}>Multi-Select</option>
                        <option value="LabBased" {{ request('type') === 'LabBased' ? 'selected' : '' }}>Lab Based</option>
                    </select>
                </div>

                <!-- Max Price -->
                <div>
                    <label class="block text-xs font-bold text-navy uppercase mb-1.5">Max Price (USD)</label>
                    <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="e.g. 30" class="w-full rounded bg-white border border-gray-300 text-xs py-2 px-2.5 text-gray-700 focus:outline-none focus:ring-1 focus:ring-cyan">
                </div>

                <!-- Buttons -->
                <button type="submit" class="w-full bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2 rounded shadow transition">
                    Apply Filters
                </button>
                
                @if(request()->has('difficulty') || request()->has('type') || request()->has('price_max'))
                    <a href="{{ url('/search?q=' . urlencode($query)) }}" class="block text-center text-xs text-red-500 hover:underline font-semibold">
                        Clear Filters
                    </a>
                @endif
            </form>
        </div>

        <!-- Results Grid (Right) -->
        <div class="lg:col-span-3 space-y-8">
            @if(count($exams) > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($exams as $exam)
                        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col justify-between hover:shadow-md transition">
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-cyan bg-opacity-15 text-navy border border-cyan border-opacity-35">{{ $exam->exam_code }}</span>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $exam->difficulty }}</span>
                                </div>
                                <h3 class="font-bold text-navy text-sm mb-2 line-clamp-2 h-10">
                                    <a href="{{ $exam->url }}" class="hover:text-cyan transition">{{ $exam->exam_name }}</a>
                                </h3>
                                <p class="text-xs text-gray-400 font-semibold mb-4">{{ $exam->vendor ? $exam->vendor->name : '' }}</p>
                            </div>
                            <div class="flex justify-between items-center pt-4 border-t border-gray-150">
                                <div class="text-xs">
                                    <span class="block text-[8px] text-gray-400 font-bold uppercase">PDF Price</span>
                                    <span class="font-bold text-navy text-sm">${{ $exam->price_pdf }}</span>
                                </div>
                                <a href="{{ $exam->url }}" class="bg-orange hover-bg-orange text-white text-[10px] font-bold py-1.5 px-3 rounded shadow transition">
                                    Get Study Pack
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Links -->
                <div class="pt-6 border-t border-gray-100">
                    {{ $exams->links() }}
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-16 text-center text-gray-500 border border-gray-200">
                    <p class="text-lg font-semibold">No certification guides found matching your query.</p>
                    <p class="text-sm text-gray-400 mt-2">Try spelling out the full vendor name or checking for typoes in the exam code.</p>
                    <a href="{{ url('/') }}" class="text-sm text-cyan hover:underline mt-4 inline-block font-semibold">Back to Home</a>
                </div>
            @endif
        </div>

    </div>
</section>
@endsection
