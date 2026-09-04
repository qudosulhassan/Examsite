{!! '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Exam Topics Base Blog</title>
        <link>{{ route('blog.index') }}</link>
        <description>Latest news, tips, and study guides for IT certifications.</description>
        <language>en-us</language>
        <pubDate>{{ now()->toRfc2822String() }}</pubDate>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('blog.rss') }}" rel="self" type="application/rss+xml"/>
        
        @foreach($posts as $post)
            <item>
                <title><![CDATA[{{ $post->title }}]]></title>
                <link>{{ route('blog.show', $post->slug) }}</link>
                <guid isPermaLink="true">{{ route('blog.show', $post->slug) }}</guid>
                <pubDate>{{ $post->published_at->toRfc2822String() }}</pubDate>
                <description><![CDATA[{{ $post->excerpt }}]]></description>
                @if($post->category)
                    <category>{{ $post->category->name }}</category>
                @endif
                <author>{{ $post->user->email ?? 'noreply@examtopicsbase.com' }} ({{ $post->user->name }})</author>
            </item>
        @endforeach
    </channel>
</rss>
