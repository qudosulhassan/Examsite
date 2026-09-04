<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = BlogCategory::withCount('posts');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.blog.categories.index', compact('categories', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug',
            'description' => 'nullable|string|max:1000',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        // Ensure unique slug
        $originalSlug = $slug;
        $count = 1;
        while (BlogCategory::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        BlogCategory::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);

        $request->validate([
            'name' => "required|string|max:255|unique:blog_categories,name,{$category->id}",
            'slug' => "nullable|string|max:255|unique:blog_categories,slug,{$category->id}",
            'description' => 'nullable|string|max:1000',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category updated successfully.');
    }

    public function destroy($id)
    {
        $category = BlogCategory::withCount('posts')->findOrFail($id);

        if ($category->posts_count > 0) {
            return redirect()->route('admin.blog-categories.index')
                ->with('error', "Cannot delete category '{$category->name}' because it has {$category->posts_count} post(s) assigned. Please reassign or delete the posts first.");
        }

        $category->delete();
        return redirect()->route('admin.blog-categories.index')->with('success', 'Category deleted successfully.');
    }
}