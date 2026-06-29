@extends('layouts.public')

@section('title', 'ExamsNinja Blog - IT Certification Tips & Industry Insights')

@section('content')
<!-- Header Banner -->
<section class="bg-navy text-white py-16 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4">
            Certification Insights & Tips
        </h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto">
            Stay up to date with the latest certification changes, exam study strategies, and tech industry career roadmaps.
        </p>
    </div>
</section>

<!-- Blog Feed Grid -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(count($posts) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                @foreach($posts as $post)
                    <article class="border border-gray-200 rounded-lg overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <!-- Placeholder image if featured_image not present -->
                            <div class="h-48 w-full bg-navy bg-opacity-5 flex items-center justify-center text-gray-400 font-semibold border-b border-gray-200">
                                @if($post->featured_image)
                                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="h-full w-full object-cover">
                                @else
                                    <span class="text-xs uppercase tracking-wider text-gray-400">ExamsNinja Article</span>
                                @endif
                            </div>
                            
                            <div class="p-6 space-y-3">
                                <span class="text-[10px] font-bold text-cyan bg-navy bg-opacity-10 px-2 py-0.5 rounded uppercase tracking-wider">{{ $post->published_at ? $post->published_at->format('M Y') : 'June 2026' }}</span>
                                <h3 class="font-bold text-navy text-lg leading-tight hover:text-cyan transition">
                                    <a href="{{ url('/blog/' . $post->slug) }}">{{ $post->title }}</a>
                                </h3>
                                <p class="text-sm text-gray-500 line-clamp-3 leading-relaxed">
                                    {{ $post->excerpt }}
                                </p>
                            </div>
                        </div>

                        <div class="px-6 pb-6 pt-4 border-t border-gray-100 flex justify-between items-center text-xs">
                            <span class="text-gray-400 font-semibold">By {{ $post->user ? $post->user->name : 'ExamsNinja' }}</span>
                            <a href="{{ url('/blog/' . $post->slug) }}" class="font-bold text-cyan hover:text-navy transition flex items-center space-x-1">
                                <span>Read post</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-6 border-t border-gray-100">
                {{ $posts->links() }}
            </div>
        @else
            <!-- Mock posts if database is empty -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Mock 1 -->
                <article class="border border-gray-200 rounded-lg overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="h-48 w-full bg-navy bg-opacity-5 flex items-center justify-center text-gray-400 border-b border-gray-200">
                            <span class="text-xs uppercase tracking-wider text-gray-400">ExamsNinja Article</span>
                        </div>
                        <div class="p-6 space-y-3">
                            <span class="text-[10px] font-bold text-cyan bg-navy bg-opacity-10 px-2 py-0.5 rounded uppercase tracking-wider">June 2026</span>
                            <h3 class="font-bold text-navy text-lg leading-tight">
                                How to Prepare for the AWS Solutions Architect Associate (SAA-C03) in 30 Days
                            </h3>
                            <p class="text-sm text-gray-500 line-clamp-3 leading-relaxed">
                                Ready to take your AWS career to the next level? Our comprehensive study roadmap breaks down exactly how to prepare for and pass the SAA-C03 in just 4 weeks.
                            </p>
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-4 border-t border-gray-100 flex justify-between items-center text-xs">
                        <span class="text-gray-400 font-semibold font-medium">By Admin</span>
                        <span class="font-bold text-cyan">Coming soon</span>
                    </div>
                </article>

                <!-- Mock 2 -->
                <article class="border border-gray-200 rounded-lg overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="h-48 w-full bg-navy bg-opacity-5 flex items-center justify-center text-gray-400 border-b border-gray-200">
                            <span class="text-xs uppercase tracking-wider text-gray-400">ExamsNinja Article</span>
                        </div>
                        <div class="p-6 space-y-3">
                            <span class="text-[10px] font-bold text-cyan bg-navy bg-opacity-10 px-2 py-0.5 rounded uppercase tracking-wider">June 2026</span>
                            <h3 class="font-bold text-navy text-lg leading-tight">
                                Top 5 IT Security Certifications to boost your Cyber Career in 2026
                            </h3>
                            <p class="text-sm text-gray-500 line-clamp-3 leading-relaxed">
                                Cyber security professionals are in higher demand than ever. Discover which five certifications offer the best salary ROI and hiring advantages this year.
                            </p>
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-4 border-t border-gray-100 flex justify-between items-center text-xs">
                        <span class="text-gray-400 font-semibold font-medium">By Admin</span>
                        <span class="font-bold text-cyan">Coming soon</span>
                    </div>
                </article>

                <!-- Mock 3 -->
                <article class="border border-gray-200 rounded-lg overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="h-48 w-full bg-navy bg-opacity-5 flex items-center justify-center text-gray-400 border-b border-gray-200">
                            <span class="text-xs uppercase tracking-wider text-gray-400">ExamsNinja Article</span>
                        </div>
                        <div class="p-6 space-y-3">
                            <span class="text-[10px] font-bold text-cyan bg-navy bg-opacity-10 px-2 py-0.5 rounded uppercase tracking-wider">May 2026</span>
                            <h3 class="font-bold text-navy text-lg leading-tight">
                                Understanding the new Microsoft Role-Based Certification blueprint updates
                            </h3>
                            <p class="text-sm text-gray-500 line-clamp-3 leading-relaxed">
                                Microsoft has recently modified the curriculum for several Azure exams. Read our breakdown of what changed, which topics were dropped, and what you must study.
                            </p>
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-4 border-t border-gray-100 flex justify-between items-center text-xs">
                        <span class="text-gray-400 font-semibold font-medium">By Admin</span>
                        <span class="font-bold text-cyan">Coming soon</span>
                    </div>
                </article>
            </div>
        @endif
    </div>
</section>
@endsection
