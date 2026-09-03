<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Exam;
use App\Models\BlogPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML sitemap.
     */
    public function index(): Response
    {
        $urls = [];

        // Static pages
        $staticRoutes = [
            'home',
            'vendors.index',
            'pricing',
            'free-demo.index',
            'faq',
            'about',
            'contact',
            'blog.index',
        ];

        foreach ($staticRoutes as $route) {
            $urls[] = [
                'loc' => route($route),
                'priority' => ($route === 'home') ? '1.0' : '0.8',
                'changefreq' => 'daily',
                'lastmod' => now()->format('Y-m-d'),
            ];
        }

        // Vendor Pages
        $vendors = Vendor::where('is_active', true)->get();
        foreach ($vendors as $vendor) {
            $urls[] = [
                'loc' => route('vendors.show', $vendor->slug),
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $vendor->updated_at->format('Y-m-d'),
            ];
        }

        // Exam Pages
        $exams = Exam::where('is_active', true)->with('vendor')->get();
        foreach ($exams as $exam) {
            $urls[] = [
                'loc' => route('exams.show', ['vendor' => $exam->vendor ? $exam->vendor->slug : 'exam', 'slug' => $exam->slug]),
                'priority' => '0.9',
                'changefreq' => 'weekly',
                'lastmod' => $exam->last_updated_at ? $exam->last_updated_at->format('Y-m-d') : $exam->updated_at->format('Y-m-d'),
            ];
        }

        // Blog Posts
        $posts = BlogPost::where('is_published', true)->get();
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post->slug),
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => $post->published_at ? $post->published_at->format('Y-m-d') : $post->updated_at->format('Y-m-d'),
            ];
        }

        $xml = view('pages.sitemap', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
