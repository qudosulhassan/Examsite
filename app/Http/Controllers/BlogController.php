<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::with(['user', 'category'])
            ->where('status', 'published')
            ->orderBy('published_at', 'desc');

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
        }

        $posts = $query->paginate(12);
        
        $categories = BlogCategory::withCount('posts')->get();
        $tags = BlogTag::all();
        $featuredPosts = BlogPost::where('status', 'published')->where('is_featured', true)->latest()->take(3)->get();

        return view('blog.index', compact('posts', 'categories', 'tags', 'featuredPosts'));
    }

    public function show($slug)
    {
        $post = BlogPost::with(['user', 'category', 'tags'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment views
        $post->increment('views_count');

        $relatedPosts = BlogPost::where('status', 'published')
            ->where('id', '!=', $post->id)
            ->where(function($q) use ($post) {
                if ($post->category_id) {
                    $q->where('category_id', $post->category_id);
                }
            })
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'relatedPosts'));
    }
}
