<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\AuditLogService;

class BlogAdminController extends Controller
{
    /**
     * Display a listing of blog posts with tabs, search, filters, and sorting.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $filter = $request->get('filter');
        $sort = $request->get('sort', 'latest');

        // Dynamic tab counts
        $counts = [
            'all' => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                })->count(),
            'draft' => BlogPost::where('status', 'draft')->count(),
            'scheduled' => BlogPost::where(function ($q) {
                $q->where('status', 'scheduled')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'published')->where('published_at', '>', now());
                  });
            })->count(),
            'trash' => BlogPost::onlyTrashed()->count(),
        ];

        if ($status === 'trash') {
            $query = BlogPost::onlyTrashed()->with(['category', 'user', 'comments']);
        } else {
            $query = BlogPost::with(['category', 'user', 'comments']);

            if ($status === 'published') {
                $query->where('status', 'published')
                      ->where(function ($q) {
                          $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                      });
            } elseif ($status === 'draft') {
                $query->where('status', 'draft');
            } elseif ($status === 'scheduled') {
                $query->where(function ($q) {
                    $q->where('status', 'scheduled')
                      ->orWhere(function ($q2) {
                          $q2->where('status', 'published')->where('published_at', '>', now());
                      });
                });
            }
        }

        // Special health filters from Dashboard
        if ($filter === 'missing_meta') {
            $query->where(function ($q) {
                $q->whereNull('meta_description')->orWhere('meta_description', '');
            });
        } elseif ($filter === 'missing_image') {
            $query->where(function ($q) {
                $q->whereNull('featured_image')->orWhere('featured_image', '');
            });
        } elseif ($filter === 'uncategorized') {
            $query->whereNull('category_id');
        }

        // Category filter
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Server-side search across Title, Slug, Excerpt, Content, Author, Category
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uQ) use ($search) {
                      $uQ->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('category', function ($cQ) use ($search) {
                      $cQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        switch ($sort) {
            case 'oldest':
                $query->orderBy('id', 'asc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'views_desc':
                $query->orderBy('views_count', 'desc');
                break;
            case 'updated':
                $query->orderBy('updated_at', 'desc');
                break;
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog.index', compact('posts', 'categories', 'counts', 'status', 'search', 'categoryId', 'sort', 'filter'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $exams = Exam::orderBy('exam_code')->select(['id', 'exam_code', 'exam_name', 'vendor_id'])->with('vendor:id,name')->get();
        $users = User::orderBy('name')->get();

        return view('admin.blog.create', compact('categories', 'tags', 'exams', 'users'));
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'featured_image' => 'nullable|string|max:500',
            'featured_image_alt' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|string|max:500',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:1000',
            'og_image' => 'nullable|string|max:500',
            'related_exam_id' => 'nullable|exists:exams,id',
            'category_id' => 'nullable|integer',
            'published_at' => 'nullable|date',
        ]);

        // Resolve slug and ensure uniqueness
        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;
        while (BlogPost::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        // Image handling: direct file upload takes precedence over media picker URL
        $imagePath = $request->featured_image;
        if ($request->hasFile('featured_image_file')) {
            $uploaded = $request->file('featured_image_file')->store('blog', 'public');
            $imagePath = Storage::url($uploaded);
        }

        // Word count & reading time
        $wordCount = str_word_count(strip_tags($request->content));
        $readingTime = max(1, (int) ceil($wordCount / 200));

        // Published at logic
        $publishedAt = null;
        if ($request->status === 'published') {
            $publishedAt = $request->filled('published_at') ? $request->published_at : now();
        } elseif ($request->status === 'scheduled') {
            $publishedAt = $request->filled('published_at') ? $request->published_at : now()->addDay();
        }

        $post = BlogPost::create([
            'user_id' => $request->user_id ?? auth()->id(),
            'category_id' => $request->category_id ?: null,
            'related_exam_id' => $request->related_exam_id ?: null,
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt ?? Str::words(strip_tags($request->content), 25),
            'content' => $request->content,
            'featured_image' => $imagePath,
            'featured_image_alt' => $request->featured_image_alt,
            'reading_time' => $readingTime,
            'meta_title' => $request->meta_title ?? $request->title,
            'meta_description' => $request->meta_description ?? ($request->excerpt ?? Str::words(strip_tags($request->content), 25)),
            'meta_keywords' => $request->meta_keywords,
            'canonical_url' => $request->canonical_url,
            'og_title' => $request->og_title ?? ($request->meta_title ?? $request->title),
            'og_description' => $request->og_description ?? ($request->meta_description ?? $request->excerpt),
            'og_image' => $request->og_image ?? $imagePath,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured'),
            'published_at' => $publishedAt,
        ]);

        // Sync tags
        if ($request->has('tags') && is_array($request->tags)) {
            $tagIds = $this->syncTags($request->tags);
            $post->tags()->sync($tagIds);
        }

        if (class_exists(AuditLogService::class)) {
            AuditLogService::log('create_blog_post', "Created blog post #{$post->id}: {$post->title}", auth()->id(), ['post_id' => $post->id]);
        }

        return redirect()->route('admin.blog.index')->with('success', "Blog post '{$post->title}' created successfully.");
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(int $id)
    {
        $post = BlogPost::with(['tags', 'category', 'user'])->findOrFail($id);
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $exams = Exam::orderBy('exam_code')->select(['id', 'exam_code', 'exam_name', 'vendor_id'])->with('vendor:id,name')->get();
        $users = User::orderBy('name')->get();

        return view('admin.blog.edit', compact('post', 'categories', 'tags', 'exams', 'users'));
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, int $id)
    {
        $post = BlogPost::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'featured_image' => 'nullable|string|max:500',
            'featured_image_alt' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|string|max:500',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:1000',
            'og_image' => 'nullable|string|max:500',
            'related_exam_id' => 'nullable|exists:exams,id',
            'category_id' => 'nullable|integer',
            'published_at' => 'nullable|date',
        ]);

        // Slug handling
        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title);
        if ($slug !== $post->slug) {
            $originalSlug = $slug;
            $count = 1;
            while (BlogPost::withTrashed()->where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
        }

        // Image handling
        $imagePath = $request->featured_image ?? $post->featured_image;
        if ($request->hasFile('featured_image_file')) {
            $uploaded = $request->file('featured_image_file')->store('blog', 'public');
            $imagePath = Storage::url($uploaded);
        }

        $wordCount = str_word_count(strip_tags($request->content));
        $readingTime = max(1, (int) ceil($wordCount / 200));

        // Published at logic
        $publishedAt = $post->published_at;
        if ($request->status === 'published') {
            $publishedAt = $request->filled('published_at') ? $request->published_at : ($post->published_at ?? now());
        } elseif ($request->status === 'scheduled') {
            $publishedAt = $request->filled('published_at') ? $request->published_at : now()->addDay();
        }

        $post->update([
            'user_id' => $request->user_id ?? $post->user_id,
            'category_id' => $request->category_id ?: null,
            'related_exam_id' => $request->related_exam_id ?: null,
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt ?? Str::words(strip_tags($request->content), 25),
            'content' => $request->content,
            'featured_image' => $imagePath,
            'featured_image_alt' => $request->featured_image_alt,
            'reading_time' => $readingTime,
            'meta_title' => $request->meta_title ?? $request->title,
            'meta_description' => $request->meta_description ?? ($request->excerpt ?? Str::words(strip_tags($request->content), 25)),
            'meta_keywords' => $request->meta_keywords,
            'canonical_url' => $request->canonical_url,
            'og_title' => $request->og_title ?? ($request->meta_title ?? $request->title),
            'og_description' => $request->og_description ?? ($request->meta_description ?? $request->excerpt),
            'og_image' => $request->og_image ?? $imagePath,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured'),
            'published_at' => $publishedAt,
        ]);

        if ($request->has('tags') && is_array($request->tags)) {
            $tagIds = $this->syncTags($request->tags);
            $post->tags()->sync($tagIds);
        } else {
            $post->tags()->sync([]);
        }

        if (class_exists(AuditLogService::class)) {
            AuditLogService::log('update_blog_post', "Updated blog post #{$post->id}: {$post->title}", auth()->id(), ['post_id' => $post->id]);
        }

        return redirect()->route('admin.blog.index')->with('success', "Blog post '{$post->title}' updated successfully.");
    }

    /**
     * Soft delete (move to Trash).
     */
    public function destroy(int $id)
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        if (class_exists(AuditLogService::class)) {
            AuditLogService::log('trash_blog_post', "Moved blog post #{$post->id} to trash", auth()->id(), ['post_id' => $post->id]);
        }

        return redirect()->route('admin.blog.index')->with('success', "Post '{$post->title}' moved to trash.");
    }

    /**
     * Restore from Trash.
     */
    public function restore($id)
    {
        $post = BlogPost::onlyTrashed()->findOrFail($id);
        $post->restore();

        if (class_exists(AuditLogService::class)) {
            AuditLogService::log('restore_blog_post', "Restored blog post #{$post->id} from trash", auth()->id(), ['post_id' => $post->id]);
        }

        return redirect()->route('admin.blog.index', ['status' => 'trash'])->with('success', "Post '{$post->title}' restored successfully.");
    }

    /**
     * Permanently delete post from database.
     */
    public function forceDelete($id)
    {
        $post = BlogPost::onlyTrashed()->findOrFail($id);
        $title = $post->title;

        $post->tags()->detach();
        $post->comments()->delete();
        $post->forceDelete();

        if (class_exists(AuditLogService::class)) {
            AuditLogService::log('force_delete_blog_post', "Permanently deleted blog post #{$id}: {$title}", auth()->id(), ['post_id' => $id]);
        }

        return redirect()->route('admin.blog.index', ['status' => 'trash'])->with('success', "Post '{$title}' permanently deleted.");
    }

    /**
     * Duplicate an existing post into a new Draft.
     */
    public function duplicate($id)
    {
        $original = BlogPost::with('tags')->findOrFail($id);

        $newSlug = $original->slug . '-draft';
        $originalSlug = $newSlug;
        $count = 1;
        while (BlogPost::withTrashed()->where('slug', $newSlug)->exists()) {
            $newSlug = "{$originalSlug}-{$count}";
            $count++;
        }

        $duplicate = BlogPost::create([
            'user_id' => auth()->id(),
            'category_id' => $original->category_id,
            'related_exam_id' => $original->related_exam_id,
            'title' => "Copy of " . $original->title,
            'slug' => $newSlug,
            'excerpt' => $original->excerpt,
            'content' => $original->content,
            'featured_image' => $original->featured_image,
            'featured_image_alt' => $original->featured_image_alt,
            'meta_title' => $original->meta_title ? "Copy of " . $original->meta_title : null,
            'meta_description' => $original->meta_description,
            'meta_keywords' => $original->meta_keywords,
            'canonical_url' => null,
            'og_title' => $original->og_title,
            'og_description' => $original->og_description,
            'og_image' => $original->og_image,
            'status' => 'draft',
            'published_at' => null,
            'views_count' => 0,
            'is_featured' => false,
            'reading_time' => $original->reading_time,
        ]);

        // Copy tags
        $duplicate->tags()->sync($original->tags->pluck('id')->toArray());

        return redirect()->route('admin.blog.edit', $duplicate->id)->with('success', "Duplicate created as Draft! You can now edit it.");
    }

    /**
     * Bulk action handler for posts.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'integer',
            'action' => 'required|in:publish,draft,trash,restore,force_delete',
        ]);

        $ids = $request->post_ids;
        $action = $request->action;

        if ($action === 'publish') {
            BlogPost::whereIn('id', $ids)->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
            return back()->with('success', count($ids) . ' post(s) published.');
        } elseif ($action === 'draft') {
            BlogPost::whereIn('id', $ids)->update(['status' => 'draft']);
            return back()->with('success', count($ids) . ' post(s) set to draft.');
        } elseif ($action === 'trash') {
            BlogPost::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' post(s) moved to trash.');
        } elseif ($action === 'restore') {
            BlogPost::onlyTrashed()->whereIn('id', $ids)->restore();
            return back()->with('success', count($ids) . ' post(s) restored from trash.');
        } elseif ($action === 'force_delete') {
            $posts = BlogPost::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($posts as $post) {
                $post->tags()->detach();
                $post->comments()->delete();
                $post->forceDelete();
            }
            return back()->with('success', count($ids) . ' post(s) permanently deleted.');
        }

        return back();
    }

    /**
     * AJAX quick category creation without page reload.
     */
    public function quickCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (BlogCategory::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $category = BlogCategory::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => "Category '{$category->name}' created successfully.",
        ]);
    }

    /**
     * AJAX quick tag creation without page reload.
     */
    public function quickTag(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);
        $tag = BlogTag::firstOrCreate(
            ['slug' => $slug],
            ['name' => $request->name]
        );

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'message' => "Tag '{$tag->name}' is ready.",
        ]);
    }

    /**
     * Helper to resolve tags.
     */
    private function syncTags(array $tags): array
    {
        $tagIds = [];
        foreach ($tags as $tagInput) {
            if (is_numeric($tagInput) && BlogTag::find($tagInput)) {
                $tagIds[] = (int) $tagInput;
            } elseif (is_string($tagInput) && trim($tagInput) !== '') {
                $slug = Str::slug($tagInput);
                $tag = BlogTag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => trim($tagInput)]
                );
                $tagIds[] = $tag->id;
            }
        }
        return array_unique($tagIds);
    }
}