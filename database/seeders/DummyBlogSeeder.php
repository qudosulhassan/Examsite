<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyBlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user or create one
        $user = \App\Models\User::first() ?? \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@examtopicsbase.com',
            'password' => bcrypt('password'),
        ]);

        $exam = \App\Models\Exam::first();

        // Create Categories
        $categories = [
            'Certification News',
            'Study Guides',
            'Exam Tips',
            'Career Advice'
        ];

        $catIds = [];
        foreach ($categories as $cat) {
            $catIds[] = \App\Models\BlogCategory::firstOrCreate([
                'slug' => \Illuminate\Support\Str::slug($cat)
            ], [
                'name' => $cat,
                'description' => 'All about ' . $cat
            ])->id;
        }

        // Create Tags
        $tags = ['AWS', 'Microsoft', 'Cisco', 'CompTIA', 'Cloud', 'Networking', 'Security'];
        $tagIds = [];
        foreach ($tags as $tag) {
            $tagIds[] = \App\Models\BlogTag::firstOrCreate([
                'slug' => \Illuminate\Support\Str::slug($tag)
            ], [
                'name' => $tag
            ])->id;
        }

        // Create Posts
        $posts = [
            [
                'title' => 'Top 10 Tips for Passing Your Next IT Certification',
                'excerpt' => 'Discover the secret strategies that over 100,000 students have used to ace their IT certification exams on the first try.',
                'content' => '<h2>1. Understand the Exam Objectives</h2><p>Before you even open a book, read the official exam objectives...</p><h2>2. Use Practice Exams</h2><p>Our Exam Topics Base test engine is the best way to prepare...</p>',
                'is_featured' => true,
            ],
            [
                'title' => 'AWS Certified Solutions Architect: 2026 Updates',
                'excerpt' => 'Everything you need to know about the latest changes to the AWS Certified Solutions Architect Associate exam.',
                'content' => '<h2>What has changed?</h2><p>AWS has updated the exam format to include more scenario-based questions focusing on serverless architectures...</p>',
                'is_featured' => false,
            ],
            [
                'title' => 'How to Break into Cybersecurity with Zero Experience',
                'excerpt' => 'A complete roadmap for beginners looking to start a career in cybersecurity, starting with the CompTIA Security+ certification.',
                'content' => '<p>Cybersecurity is one of the fastest-growing fields in tech. Here is how you can get started...</p>',
                'is_featured' => false,
            ],
            [
                'title' => 'Cisco CCNA vs. CompTIA Network+: Which Should You Take?',
                'excerpt' => 'Comparing the two most popular entry-level networking certifications to help you decide which one is right for your career path.',
                'content' => '<p>Both CCNA and Network+ are excellent certifications, but they serve different purposes...</p>',
                'is_featured' => true,
            ],
            [
                'title' => 'The Future of Cloud Computing Certifications',
                'excerpt' => 'As multi-cloud strategies become the norm, we look at the most valuable cloud certifications to earn this year.',
                'content' => '<p>Companies are no longer relying on a single cloud provider. Here are the top certifications to prove your multi-cloud expertise...</p>',
                'is_featured' => false,
            ]
        ];

        foreach ($posts as $index => $postData) {
            $post = \App\Models\BlogPost::create([
                'user_id' => $user->id,
                'category_id' => $catIds[array_rand($catIds)],
                'title' => $postData['title'],
                'slug' => \Illuminate\Support\Str::slug($postData['title']),
                'excerpt' => $postData['excerpt'],
                'content' => $postData['content'],
                'status' => 'published',
                'published_at' => now()->subDays(rand(1, 30)),
                'views_count' => rand(100, 5000),
                'is_featured' => $postData['is_featured'],
                'reading_time' => rand(3, 10),
                'related_exam_id' => $exam ? $exam->id : null,
                'featured_image' => 'https://images.unsplash.com/photo-' . (1500000000000 + $index * 100000) . '?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            ]);

            // Attach 2 random tags
            $randomTags = (array) array_rand(array_flip($tagIds), 2);
            $post->tags()->attach($randomTags);
            
            // Add some comments
            \App\Models\BlogComment::create([
                'blog_post_id' => $post->id,
                'author_name' => 'John Doe',
                'author_email' => 'john@example.com',
                'comment_text' => 'Great article! Really helped me prepare.',
                'status' => 'approved'
            ]);
            \App\Models\BlogComment::create([
                'blog_post_id' => $post->id,
                'author_name' => 'Jane Smith',
                'author_email' => 'jane@example.com',
                'comment_text' => 'I have a question about point 2...',
                'status' => 'approved'
            ]);
        }
    }
}
