<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogTagController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = BlogTag::withCount('posts');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $tags = $query->orderBy('name')->paginate(25)->withQueryString();
        return view('admin.blog.tags.index', compact('tags', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_tags,name',
            'slug' => 'nullable|string|max:255|unique:blog_tags,slug',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (BlogTag::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        BlogTag::create([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return redirect()->route('admin.blog-tags.index')->with('success', 'Blog tag created successfully.');
    }

    public function update(Request $request, $id)
    {
        $tag = BlogTag::findOrFail($id);

        $request->validate([
            'name' => "required|string|max:255|unique:blog_tags,name,{$tag->id}",
            'slug' => "nullable|string|max:255|unique:blog_tags,slug,{$tag->id}",
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        $tag->update([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return redirect()->route('admin.blog-tags.index')->with('success', 'Blog tag updated successfully.');
    }

    public function destroy($id)
    {
        $tag = BlogTag::findOrFail($id);
        $tag->posts()->detach();
        $tag->delete();
        return redirect()->route('admin.blog-tags.index')->with('success', 'Tag deleted successfully.');
    }
}