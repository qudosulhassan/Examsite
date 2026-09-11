<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VendorFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->company();
        return [
            'name'       => $name,
            'slug'       => Str::slug($name),
            'category'   => $this->faker->randomElement(['Cloud', 'Security', 'Networking', 'Other']),
            'exam_count' => 0,
            'sort_order' => 0,
            'is_active'  => true,
        ];
    }
}
