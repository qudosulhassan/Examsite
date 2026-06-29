@extends('layouts.public')

@section('title', $post->meta_title ?? "{$post->title} - ExamsNinja Blog")
@section('meta_description', $post->meta_description ?? $post->excerpt)
@section('meta_keywords', $post->meta_keywords ?? "it blog, certification tips, tech news, {$post->title}")
@section('canonical_url', route('blog.show', $post->slug))
@section('og_type', 'article')

@section('seo_tags')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BlogPosting",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{{ route('blog.show', $post->slug) }}"
  },
  "headline": "{{ $post->title }}",
  "description": "{{ $post->meta_description ?? $post->excerpt }}",
  "image": "{{ $post->featured_image ?? asset('images/og-image.jpg') }}",
  "author": {
    "@type": "Person",
    "name": "{{ $post->user ? $post->user->name : 'ExamsNinja Expert' }}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "ExamsNinja",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('images/logo.png') }}"
    }
  },
  "datePublished": "{{ $post->published_at ? $post->published_at->toIso8601String() : now()->toIso8601String() }}",
  "dateModified": "{{ $post->updated_at ? $post->updated_at->toIso8601String() : now()->toIso8601String() }}"
}
</script>
@endsection

@section('content')
<!-- Header Area -->
<section class="bg-navy text-white py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <!-- Breadcrumbs -->
        <div class="mb-6 flex justify-center">
            <x-breadcrumbs :links="[
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'Blog', 'url' => '/blog'],
                ['name' => $post->title, 'url' => '']
            ]" />
        </div>

        <!-- Category Badge -->
        <span class="inline-block text-[10px] font-bold text-cyan bg-white bg-opacity-10 border border-cyan border-opacity-35 px-3 py-1 rounded uppercase tracking-wider">
            IT Industry Insights
        </span>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
            {{ $post->title }}
        </h1>
        
        <div class="flex items-center justify-center space-x-4 text-sm text-gray-300 pt-2 font-medium">
            <span class="flex items-center">By <strong class="ml-1 text-white">{{ $post->user ? $post->user->name : 'ExamsNinja' }}</strong></span>
            <span>•</span>
            <span>Published: {{ $post->published_at ? $post->published_at->format('F d, Y') : 'June 19, 2026' }}</span>
        </div>
    </div>
</section>

<!-- Main content article body -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Post content -->
        <article class="prose prose-navy max-w-none text-navy text-sm sm:text-base leading-relaxed space-y-6">
            @if($post->featured_image)
                <div class="mb-8 rounded-lg overflow-hidden max-h-96 shadow-sm border border-gray-150">
                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                </div>
            @endif

            {!! $post->content !!}
        </article>

        <!-- Related posts section footer -->
        <div class="border-t border-gray-200 mt-16 pt-12">
            <h3 class="text-xl font-bold text-navy mb-8">Related Articles</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedPosts as $relPost)
                    <div class="border border-gray-250 rounded-lg p-5 flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block mb-2">{{ $relPost->published_at ? $relPost->published_at->format('M d, Y') : 'June 2026' }}</span>
                            <h4 class="font-bold text-navy text-sm leading-tight mb-2 line-clamp-2 h-10">
                                <a href="{{ url('/blog/' . $relPost->slug) }}" class="hover:text-cyan transition">{{ $relPost->title }}</a>
                            </h4>
                        </div>
                        <a href="{{ url('/blog/' . $relPost->slug) }}" class="text-[10px] font-bold text-cyan hover:text-navy transition flex items-center space-x-1 mt-4">
                            <span>Read article</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</section>
@endsection
