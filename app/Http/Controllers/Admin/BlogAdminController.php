<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Support\Facades\Storage;

class BlogAdminController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('category')->orderBy('id', 'desc')->paginate(10);
        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $exams = \App\Models\Exam::orderBy('exam_code')->get();
        $users = \App\Models\User::orderBy('name')->get();
        return view('admin.blog.create', compact('categories', 'tags', 'exams', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'related_exam_id' => 'nullable|exists:exams,id',
        ]);

        // Handle Dynamic Category Creation
        $categoryId = null;
        if ($request->filled('category_id')) {
            if (is_numeric($request->category_id) && BlogCategory::find($request->category_id)) {
                $categoryId = $request->category_id;
            } else {
                $newCat = BlogCategory::firstOrCreate(
                    ['slug' => Str::slug($request->category_id)],
                    ['name' => $request->category_id]
                );
                $categoryId = $newCat->id;
            }
        }

        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('blog', 'public');
        }
        
        $wordCount = str_word_count(strip_tags($request->content));
        $readingTime = max(1, ceil($wordCount / 200));

        $post = BlogPost::create([
            'user_id' => $request->user_id ?? auth()->id(),
            'category_id' => $categoryId,
            'related_exam_id' => $request->related_exam_id,
            'title' => $request->title,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->title),
            'excerpt' => $request->excerpt ?? Str::words(strip_tags($request->content), 20),
            'content' => $request->content,
            'featured_image' => $imagePath,
            'reading_time' => $readingTime,
            'meta_title' => $request->meta_title ?? $request->title,
            'meta_description' => $request->meta_description ?? $request->excerpt,
            'meta_keywords' => $request->meta_keywords,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured'),
            'published_at' => $request->published_at ?? ($request->status === 'published' ? now() : null),
        ]);

        if ($request->has('tags')) {
            $tagIds = $this->syncTags($request->tags);
            $post->tags()->sync($tagIds);
        }

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created successfully.');
    }

    public function edit(int $id)
    {
        $post = BlogPost::with('tags')->findOrFail($id);
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $exams = \App\Models\Exam::orderBy('exam_code')->get();
        $users = \App\Models\User::orderBy('name')->get();
        return view('admin.blog.edit', compact('post', 'categories', 'tags', 'exams', 'users'));
    }

    public function update(Request $request, int $id)
    {
        $post = BlogPost::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'related_exam_id' => 'nullable|exists:exams,id',
        ]);

        // Handle Dynamic Category Creation
        $categoryId = null;
        if ($request->filled('category_id')) {
            if (is_numeric($request->category_id) && BlogCategory::find($request->category_id)) {
                $categoryId = $request->category_id;
            } else {
                $newCat = BlogCategory::firstOrCreate(
                    ['slug' => Str::slug($request->category_id)],
                    ['name' => $request->category_id]
                );
                $categoryId = $newCat->id;
            }
        }

        $imagePath = $post->featured_image;
        if ($request->hasFile('featured_image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('featured_image')->store('blog', 'public');
        }
        
        $wordCount = str_word_count(strip_tags($request->content));
        $readingTime = max(1, ceil($wordCount / 200));

        $post->update([
            'user_id' => $request->user_id ?? $post->user_id,
            'title' => $request->title,
            'category_id' => $categoryId,
            'related_exam_id' => $request->related_exam_id,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->title),
            'excerpt' => $request->excerpt ?? Str::words(strip_tags($request->content), 20),
            'content' => $request->content,
            'featured_image' => $imagePath,
            'reading_time' => $readingTime,
            'meta_title' => $request->meta_title ?? $request->title,
            'meta_description' => $request->meta_description ?? $request->excerpt,
            'meta_keywords' => $request->meta_keywords,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured'),
            'published_at' => $request->published_at ?? ($request->status === 'published' && !$post->published_at ? now() : $post->published_at),
        ]);

        if ($request->has('tags')) {
            $tagIds = $this->syncTags($request->tags);
            $post->tags()->sync($tagIds);
        } else {
            $post->tags()->sync([]);
        }

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated successfully.');
    }

    public function destroy(int $id)
    {
        $post = BlogPost::findOrFail($id);
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        $post->delete();
        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted successfully.');
    }

    /**
     * Process tags (create new ones if needed) and return their IDs
     */
    private function syncTags(array $tags): array
    {
        $tagIds = [];
        foreach ($tags as $tagInput) {
            if (is_numeric($tagInput) && BlogTag::find($tagInput)) {
                $tagIds[] = $tagInput;
            } else {
                $newTag = BlogTag::firstOrCreate(
                    ['slug' => Str::slug($tagInput)],
                    ['name' => $tagInput]
                );
                $tagIds[] = $newTag->id;
            }
        }
        return $tagIds;
    }
}
