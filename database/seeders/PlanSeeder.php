<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'price_monthly' => 19.00,
                'price_annual' => 99.00,
                'features' => ['Access to 50 exams', '1 active session', 'Standard support'],
            ],
            [
                'name' => 'Pro',
                'price_monthly' => 39.00,
                'price_annual' => 179.00,
                'features' => ['Access to all exams', '2 active sessions', 'Progress reports', 'Priority support'],
            ],
            [
                'name' => 'Ultimate',
                'price_monthly' => 59.00,
                'price_annual' => 249.00,
                'features' => ['Access to all exams', 'All PDFs included', 'Unlimited sessions', 'Priority support'],
            ],
            [
                'name' => 'Team',
                'price_monthly' => 149.00,
                'price_annual' => 599.00,
                'features' => ['5 user seats', 'Team dashboard', 'Per-member reports', 'Dedicated account manager'],
            ],
        ];

        Setting::set('subscription_plans', json_encode($plans));

        // Seed other general settings too as a senior developer move
        Setting::set('site_name', 'ExamsNinja');
        Setting::set('site_tagline', 'Pass Like a Ninja. First Attempt Guaranteed.');
        Setting::set('contact_email', 'contact@examsninja.com');
        Setting::set('support_email', 'support@examsninja.com');
        Setting::set('maintenance_mode', 'false');
    }
}
