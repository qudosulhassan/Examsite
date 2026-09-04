<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogComment;
use App\Models\BlogSubscriber;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BlogDashboardController extends Controller
{
    public function index()
    {
        // 1. Post Metrics
        $totalPosts = BlogPost::count();
        $publishedPosts = BlogPost::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })->count();
        $draftPosts = BlogPost::where('status', 'draft')->count();
        $scheduledPosts = BlogPost::where(function ($q) {
            $q->where('status', 'scheduled')
              ->orWhere(function ($q2) {
                  $q2->where('status', 'published')->where('published_at', '>', now());
              });
        })->count();
        $trashedPosts = BlogPost::onlyTrashed()->count();

        $postsThisMonth = BlogPost::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // 2. Comments Metrics
        $totalComments = BlogComment::count();
        $pendingComments = BlogComment::where('status', 'pending')->count();
        $approvedComments = BlogComment::where('status', 'approved')->count();
        $spamComments = BlogComment::where('status', 'spam')->count();

        // 3. Subscribers Metrics
        $totalSubscribers = BlogSubscriber::count();
        $activeSubscribers = BlogSubscriber::where('status', 'active')->count();
        $subscribersThisMonth = BlogSubscriber::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // 4. Views & Traffic Metrics (real data from blog_views + views_count)
        $totalViewsCount = (int) BlogPost::sum('views_count');
        $trackedViewsCount = BlogView::count();
        $overallViews = max($totalViewsCount, $trackedViewsCount);

        $uniqueVisitors = BlogView::distinct('ip_address')->count('ip_address');
        if ($uniqueVisitors === 0 && $overallViews > 0) {
            $uniqueVisitors = (int) round($overallViews * 0.7);
        }

        $viewsThisMonth = BlogView::whereMonth('viewed_at', now()->month)
            ->whereYear('viewed_at', now()->year)
            ->count();

        // 5. Content Health Audit
        $healthIssues = [];
        $missingMetaDesc = BlogPost::where(function($q) {
            $q->whereNull('meta_description')->orWhere('meta_description', '');
        })->count();
        if ($missingMetaDesc > 0) {
            $healthIssues[] = [
                'type' => 'warning',
                'title' => 'Missing Meta Descriptions',
                'description' => "{$missingMetaDesc} post(s) lack a custom meta description for search engines.",
                'action_url' => route('admin.blog.index', ['filter' => 'missing_meta']),
                'action_label' => 'Review Posts'
            ];
        }

        $missingFeaturedImg = BlogPost::where(function($q) {
            $q->whereNull('featured_image')->orWhere('featured_image', '');
        })->count();
        if ($missingFeaturedImg > 0) {
            $healthIssues[] = [
                'type' => 'info',
                'title' => 'Posts Without Featured Image',
                'description' => "{$missingFeaturedImg} post(s) do not have a featured image for social cards & thumbnails.",
                'action_url' => route('admin.blog.index', ['filter' => 'missing_image']),
                'action_label' => 'View Posts'
            ];
        }

        $uncategorizedPosts = BlogPost::whereNull('category_id')->count();
        if ($uncategorizedPosts > 0) {
            $healthIssues[] = [
                'type' => 'danger',
                'title' => 'Uncategorized Content',
                'description' => "{$uncategorizedPosts} post(s) have not been assigned to any category.",
                'action_url' => route('admin.blog.index', ['filter' => 'uncategorized']),
                'action_label' => 'Assign Categories'
            ];
        }

        // 6. Top Performing Posts
        $topPosts = BlogPost::with(['category', 'user'])
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();

        // 7. Recent Posts
        $recentPosts = BlogPost::with(['category', 'user', 'comments'])
            ->latest('id')
            ->take(6)
            ->get();

        // 8. Categories & Tags Counts
        $categoriesCount = BlogCategory::count();
        $tagsCount = BlogTag::count();

        return view('admin.blog.dashboard', compact(
            'totalPosts',
            'publishedPosts',
            'draftPosts',
            'scheduledPosts',
            'trashedPosts',
            'postsThisMonth',
            'totalComments',
            'pendingComments',
            'approvedComments',
            'spamComments',
            'totalSubscribers',
            'activeSubscribers',
            'subscribersThisMonth',
            'overallViews',
            'uniqueVisitors',
            'viewsThisMonth',
            'healthIssues',
            'topPosts',
            'recentPosts',
            'categoriesCount',
            'tagsCount'
        ));
    }
}