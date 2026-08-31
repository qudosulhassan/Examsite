<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\User;
use App\Models\BlogView;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $featuredPost = BlogPost::with(['user', 'category'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->latest('published_at')
            ->first();

        $postsQuery = BlogPost::with(['user', 'category'])
            ->where('status', 'published');
            
        if ($featuredPost) {
            $postsQuery->where('id', '!=', $featuredPost->id);
        }

        $posts = $postsQuery->latest('published_at')->paginate(9);

        $categories = BlogCategory::withCount('posts')->get();
        $tags = BlogTag::withCount('posts')->orderBy('posts_count', 'desc')->limit(20)->get();
        
        $popularPosts = BlogPost::where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        return view('pages.blog.index', compact('posts', 'featuredPost', 'categories', 'tags', 'popularPosts'));
    }

    public function show(Request $request, string $slug)
    {
        $post = BlogPost::with(['user', 'category', 'tags', 'exam', 'comments' => function($q) {
            $q->where('status', 'approved')->whereNull('parent_id')->with('replies');
        }])
        ->where('slug', $slug)
        ->firstOrFail();

        // If draft, only allow if authenticated admin
        if ($post->status === 'draft') {
            if (!auth()->check() || !auth()->user()->hasRole('admin')) {
                abort(404);
            }
        }

        // Track View deduplicated by IP and Session
        $ip = $request->ip();
        $sessionId = $request->session()->getId();
        
        $hasViewed = BlogView::where('blog_post_id', $post->id)
            ->where(function($q) use ($ip, $sessionId) {
                $q->where('ip_address', $ip)->orWhere('session_id', $sessionId);
            })->exists();

        if (!$hasViewed) {
            BlogView::create([
                'blog_post_id' => $post->id,
                'ip_address' => $ip,
                'session_id' => $sessionId,
            ]);
            $post->increment('views_count');
        }

        $relatedPosts = BlogPost::where('status', 'published')
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();
            
        $categories = BlogCategory::withCount('posts')->get();
        $popularPosts = BlogPost::where('status', 'published')->orderBy('views_count', 'desc')->limit(5)->get();

        return view('pages.blog.show', compact('post', 'relatedPosts', 'categories', 'popularPosts'));
    }

    public function category(string $slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();
        $posts = BlogPost::with(['user', 'category'])
            ->where('status', 'published')
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(9);

        return view('pages.blog.category', compact('category', 'posts'));
    }

    public function tag(string $slug)
    {
        $tag = BlogTag::where('slug', $slug)->firstOrFail();
        $posts = $tag->posts()
            ->with(['user', 'category'])
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(9);

        return view('pages.blog.tag', compact('tag', 'posts'));
    }

    public function author(string $slug)
    {
        // For simplicity, assuming user slug is their name sluggified, or passing ID
        // Let's use ID for author URL or add slug to User. Assuming ID for now.
        $author = User::findOrFail($slug);
        
        $posts = BlogPost::with(['user', 'category'])
            ->where('status', 'published')
            ->where('user_id', $author->id)
            ->latest('published_at')
            ->paginate(9);

        return view('pages.blog.author', compact('author', 'posts'));
    }

    public function search(Request $request)
    {
        $q = $request->input('q');
        
        $posts = BlogPost::with(['user', 'category'])
            ->where('status', 'published')
            ->where(function($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('content', 'like', "%{$q}%");
            })
            ->latest('published_at')
            ->paginate(9);

        return view('pages.blog.search', compact('posts', 'q'));
    }

    public function rss()
    {
        $posts = BlogPost::with(['user', 'category'])
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(20)
            ->get();

        return response()->view('pages.blog.rss', compact('posts'))->header('Content-Type', 'text/xml');
    }
}
