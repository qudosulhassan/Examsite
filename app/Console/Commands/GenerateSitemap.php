<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\SitemapIndex;
use App\Models\Exam;
use App\Models\Vendor;
use App\Models\BlogPost;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemaps for ExamsNinja';

    public function handle()
    {
        $this->info('Generating sitemaps...');

        // 1. Static Pages
        Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create('/vendors')->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create('/pricing')->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
            ->add(Url::create('/free-demo')->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
            ->add(Url::create('/blog')->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create('/faq')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
            ->add(Url::create('/contact')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
            ->writeToFile(public_path('sitemap-static.xml'));
        
        $this->info('Generated sitemap-static.xml');

        // 2. Vendors
        $vendorSitemap = Sitemap::create();
        Vendor::where('is_active', true)->get()->each(function (Vendor $vendor) use ($vendorSitemap) {
            $vendorSitemap->add(
                Url::create("/vendors/{$vendor->slug}")
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        });
        $vendorSitemap->writeToFile(public_path('sitemap-vendors.xml'));
        $this->info('Generated sitemap-vendors.xml');

        // 3. Exams
        $examSitemap = Sitemap::create();
        Exam::where('is_active', true)->get()->each(function (Exam $exam) use ($examSitemap) {
            $url = $exam->vendor ? "/exams/{$exam->slug}" : "/exams/{$exam->slug}";
            $examSitemap->add(
                Url::create($url)
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setLastModificationDate($exam->updated_at)
            );
        });
        $examSitemap->writeToFile(public_path('sitemap-exams.xml'));
        $this->info('Generated sitemap-exams.xml');

        // 4. Blog Posts
        $blogSitemap = Sitemap::create();
        BlogPost::where('status', 'published')->get()->each(function (BlogPost $post) use ($blogSitemap) {
            $blogSitemap->add(
                Url::create("/blog/{$post->slug}")
                    ->setPriority(0.6)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setLastModificationDate($post->updated_at)
            );
        });
        $blogSitemap->writeToFile(public_path('sitemap-blog.xml'));
        $this->info('Generated sitemap-blog.xml');

        // 5. Generate Index File
        SitemapIndex::create()
            ->add('/sitemap-static.xml')
            ->add('/sitemap-vendors.xml')
            ->add('/sitemap-exams.xml')
            ->add('/sitemap-blog.xml')
            ->writeToFile(public_path('sitemap.xml'));
            
        $this->info('Generated sitemap.xml index. All done!');
    }
}
