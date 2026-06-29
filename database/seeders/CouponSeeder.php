<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::create([
            'code' => 'NINJA20',
            'description' => 'Ninja Launch Offer - Get 20% off on all certification guides and test engines.',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'min_order_amount' => 0.00,
            'max_uses' => 1000,
            'used_count' => 0,
            'per_user_limit' => 1,
            'applicable_to' => 'all',
            'expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'NINJA10',
            'description' => '10 USD flat discount on any exam order.',
            'discount_type' => 'fixed',
            'discount_value' => 10.00,
            'min_order_amount' => 15.00,
            'max_uses' => 500,
            'used_count' => 0,
            'per_user_limit' => 1,
            'applicable_to' => 'all',
            'expires_at' => now()->addYear(),
            'is_active' => true,
        ]);
    }
}
