<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            // Subscriptions
            [
                'type' => 'subscription',
                'name' => 'Basic',
                'slug' => 'basic-subscription',
                'description' => 'For single certification builders.',
                'price_monthly' => 19.00,
                'price_annual' => 190.00,
                'price_lifetime' => null,
                'features' => [
                    'Access to 50 practice exams',
                    '1 active test engine session',
                    'Standard email support',
                ],
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'type' => 'subscription',
                'name' => 'Pro',
                'slug' => 'pro-subscription',
                'description' => 'For dedicated IT professionals.',
                'price_monthly' => 39.00,
                'price_annual' => 390.00,
                'price_lifetime' => null,
                'features' => [
                    'Access to all practice exams',
                    '2 active engine sessions',
                    'Progress history & reports',
                    'Priority email support',
                ],
                'is_popular' => true,
                'sort_order' => 2,
            ],
            [
                'type' => 'subscription',
                'name' => 'Ultimate',
                'slug' => 'ultimate-subscription',
                'description' => 'Complete dumps and test package.',
                'price_monthly' => 59.00,
                'price_annual' => 590.00,
                'price_lifetime' => null,
                'features' => [
                    'Access to all practice exams',
                    'All PDF guides included ($1000+ value)',
                    'Unlimited active engine sessions',
                    'Priority 24/7 support',
                ],
                'is_popular' => false,
                'sort_order' => 3,
            ],
            [
                'type' => 'subscription',
                'name' => 'Team (5 Seats)',
                'slug' => 'team-subscription',
                'description' => 'For corporate team development.',
                'price_monthly' => 149.00,
                'price_annual' => 1490.00,
                'price_lifetime' => null,
                'features' => [
                    '5 active user seat licenses',
                    'Centralized team dashboard',
                    'Per-member progress reports',
                    'Dedicated account manager',
                ],
                'is_popular' => false,
                'sort_order' => 4,
            ],
            // Bundles
            [
                'type' => 'bundle',
                'name' => 'Single Exam PDF',
                'slug' => 'single-exam-pdf',
                'description' => 'Access to any single certification guide of your choice.',
                'price_monthly' => null,
                'price_annual' => null,
                'price_lifetime' => 25.00,
                'features' => [
                    '1 Exam PDF file download',
                    '3 months free updates',
                    'Max 3 download attempts',
                ],
                'is_popular' => false,
                'sort_order' => 5,
            ],
            [
                'type' => 'bundle',
                'name' => 'Bundle 5 Exams',
                'slug' => 'bundle-5-exams',
                'description' => 'Choose any 5 certification guides across any vendor.',
                'price_monthly' => null,
                'price_annual' => null,
                'price_lifetime' => 59.00,
                'features' => [
                    '5 Exam PDF files downloads',
                    '3 months free updates',
                    'Lifetime access to files',
                ],
                'is_popular' => true,
                'sort_order' => 6,
            ],
            [
                'type' => 'bundle',
                'name' => 'Bundle 10 Exams',
                'slug' => 'bundle-10-exams',
                'description' => 'Choose any 10 guides across all vendors.',
                'price_monthly' => null,
                'price_annual' => null,
                'price_lifetime' => 99.00,
                'features' => [
                    '10 Exam PDF files downloads',
                    '6 months free updates',
                    'Lifetime access to files',
                ],
                'is_popular' => false,
                'sort_order' => 7,
            ],
        ];

        foreach ($packages as $package) {
            \App\Models\Package::updateOrCreate(
                ['slug' => $package['slug']],
                $package
            );
        }
    }
}
