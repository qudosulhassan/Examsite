<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Artisan;
use App\Models\Redirect;

class BlogPost extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::updating(function ($post) {
            if ($post->isDirty('slug')) {
                Redirect::create([
                    'old_url' => 'blog/' . $post->getOriginal('slug'),
                    'new_url' => 'blog/' . $post->slug,
                    'status_code' => 301,
                ]);
            }
        });

        static::saved(function ($post) {
            Artisan::call('sitemap:generate');
        });

        static::deleted(function ($post) {
            Artisan::call('sitemap:generate');
        });
    }

    protected $table = 'blog_posts';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'published_at',
        'views_count',
        'is_featured',
        'reading_time',
        'related_exam_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'reading_time' => 'integer',
    ];

    /**
     * Get the user (author) of the blog post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the blog post.
     */
    public function category()
    {
        return $this->belongsTo(BlogCategory::class);
    }

    /**
     * Get the tags for the blog post.
     */
    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    /**
     * Get the related exam.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'related_exam_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function comments()
    {
        return $this->hasMany(BlogComment::class);
    }
}
