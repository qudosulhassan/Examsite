<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ExamFactory extends Factory
{
    public function definition(): array
    {
        $code = strtoupper($this->faker->unique()->bothify('??-###'));
        return [
            'vendor_id'      => Vendor::factory(),
            'exam_code'      => $code,
            'exam_name'      => $code . ' ' . $this->faker->words(3, true),
            'slug'           => Str::slug($code . '-' . $this->faker->words(2, true)),
            'description'    => $this->faker->paragraph(),
            'question_count' => $this->faker->numberBetween(30, 200),
            'passing_score'  => 70,
            'difficulty'     => $this->faker->randomElement(['Associate', 'Professional', 'Expert']),
            'exam_type'      => 'MultipleChoice',
            'price_pdf'      => 29.99,
            'price_engine'   => 39.99,
            'is_active'      => true,
            'is_featured'    => false,
            'is_pdf_available'    => true,
            'is_engine_available' => true,
            'is_bundle_available' => true,
        ];
    }
}
