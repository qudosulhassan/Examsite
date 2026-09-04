<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogComment;
use App\Models\BlogSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BlogCmsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@examsninja.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_access_blog_dashboard_and_view_metrics()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.blog.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Blog & Content Management', false);
        $response->assertSee('Total Posts');
        $response->assertSee('Content Views');
        $response->assertSee('User Comments');
        $response->assertSee('Subscribers');
    }

    public function test_admin_can_create_post_with_seo_and_tags()
    {
        $category = BlogCategory::create(['name' => 'Cloud Testing', 'slug' => 'cloud-testing']);
        $tag = BlogTag::create(['name' => 'AWS', 'slug' => 'aws']);

        $postData = [
            'title' => 'Guide to AWS Cloud Architecture 2026',
            'slug' => 'guide-to-aws-cloud-architecture-2026',
            'content' => '<p>This is comprehensive content with valuable preparation tips.</p>',
            'excerpt' => 'A complete roadmap to modern cloud architecture.',
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'meta_title' => 'AWS Architecture 2026 Guide',
            'meta_description' => 'Comprehensive SEO guide for passing AWS exams.',
            'tags' => [$tag->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.blog.store'), $postData);
        $response->assertRedirect(route('admin.blog.index'));

        $this->assertDatabaseHas('blog_posts', [
            'title' => $postData['title'],
            'category_id' => $category->id,
            'status' => 'published',
        ]);

        $createdPost = BlogPost::where('slug', 'guide-to-aws-cloud-architecture-2026')->first();
        $this->assertNotNull($createdPost);
        $this->assertTrue($createdPost->tags->contains($tag->id));
    }

    public function test_admin_can_duplicate_post_into_draft()
    {
        $original = BlogPost::create([
            'user_id' => $this->admin->id,
            'title' => 'Original Kubernetes Article',
            'slug' => 'original-kubernetes-article',
            'content' => '<p>Kubernetes deep dive details.</p>',
            'excerpt' => 'Kubernetes overview summary.',
            'status' => 'published',
            'published_at' => now(),
            'views_count' => 120,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.blog.duplicate', $original->id));
        $duplicate = BlogPost::where('slug', 'original-kubernetes-article-draft')->first();

        $this->assertNotNull($duplicate);
        $this->assertEquals('Copy of ' . $original->title, $duplicate->title);
        $this->assertEquals('draft', $duplicate->status);
        $this->assertEquals(0, $duplicate->views_count);
        $response->assertRedirect(route('admin.blog.edit', $duplicate->id));
    }

    public function test_admin_can_soft_delete_and_restore_post()
    {
        $post = BlogPost::create([
            'user_id' => $this->admin->id,
            'title' => 'Temporary Post to Trash',
            'slug' => 'temporary-post-to-trash',
            'content' => '<p>Content</p>',
            'status' => 'draft',
        ]);

        // Move to trash
        $delResponse = $this->actingAs($this->admin)->delete(route('admin.blog.destroy', $post->id));
        $delResponse->assertRedirect(route('admin.blog.index'));
        $this->assertSoftDeleted('blog_posts', ['id' => $post->id]);

        // Restore
        $restoreResponse = $this->actingAs($this->admin)->post(route('admin.blog.restore', $post->id));
        $restoreResponse->assertRedirect(route('admin.blog.index', ['status' => 'trash']));
        $this->assertNotSoftDeleted('blog_posts', ['id' => $post->id]);
    }

    public function test_admin_bulk_actions_work_correctly()
    {
        $post1 = BlogPost::create([
            'user_id' => $this->admin->id,
            'title' => 'Bulk Post 1',
            'slug' => 'bulk-post-1',
            'content' => '<p>Test 1</p>',
            'status' => 'draft',
        ]);
        $post2 = BlogPost::create([
            'user_id' => $this->admin->id,
            'title' => 'Bulk Post 2',
            'slug' => 'bulk-post-2',
            'content' => '<p>Test 2</p>',
            'status' => 'draft',
        ]);

        // Bulk Publish
        $response = $this->actingAs($this->admin)->post(route('admin.blog.bulk-action'), [
            'post_ids' => [$post1->id, $post2->id],
            'action' => 'publish',
        ]);

        $this->assertEquals('published', $post1->fresh()->status);
        $this->assertEquals('published', $post2->fresh()->status);
    }

    public function test_cannot_delete_category_with_assigned_posts()
    {
        $category = BlogCategory::create([
            'name' => 'Protected Category',
            'slug' => 'protected-cat',
        ]);

        $post = BlogPost::create([
            'user_id' => $this->admin->id,
            'category_id' => $category->id,
            'title' => 'Linked Post to Protected Cat',
            'slug' => 'linked-post',
            'content' => '<p>Linked</p>',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.blog-categories.destroy', $category->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('blog_categories', ['id' => $category->id]);
    }

    public function test_admin_can_moderate_comments_and_reply()
    {
        $post = BlogPost::create([
            'user_id' => $this->admin->id,
            'title' => 'Commentable Article',
            'slug' => 'commentable-article',
            'content' => '<p>Discuss</p>',
            'status' => 'published',
        ]);

        $comment = BlogComment::create([
            'blog_post_id' => $post->id,
            'author_name' => 'Alice Candidate',
            'author_email' => 'alice@example.com',
            'comment_text' => 'Does this exam cover scenario questions?',
            'status' => 'pending',
        ]);

        // Approve comment
        $this->actingAs($this->admin)->patch(route('admin.blog-comments.approve', $comment->id));
        $this->assertEquals('approved', $comment->fresh()->status);

        // Reply to comment
        $replyResponse = $this->actingAs($this->admin)->post(route('admin.blog-comments.reply', $comment->id), [
            'reply_text' => 'Yes, over 40% of the questions are scenario based.',
        ]);

        $this->assertDatabaseHas('blog_comments', [
            'parent_id' => $comment->id,
            'comment_text' => 'Yes, over 40% of the questions are scenario based.',
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_export_subscribers_csv()
    {
        BlogSubscriber::create([
            'email' => 'subscriber@example.com',
            'first_name' => 'Jane',
            'status' => 'active',
            'subscribed_at' => now(),
            'source' => 'blog_newsletter',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.blog-subscribers.export'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }
}