<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'History', 'code' => 'HIST'],
            ['name' => 'Geography', 'code' => 'GEO'],
            ['name' => 'Physics', 'code' => 'PHY'],
            ['name' => 'Chemistry', 'code' => 'CHEM'],
            ['name' => 'Biology', 'code' => 'BIO'],
            ['name' => 'Art', 'code' => 'ART'],
            ['name' => 'Music', 'code' => 'MUS'],
            ['name' => 'Physical Education', 'code' => 'PE'],
        ];
        
        $subject = fake()->randomElement($subjects);
        
        return [
            'name' => $subject['name'],
            'code' => $subject['code'] . '_' . fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'credits' => fake()->numberBetween(1, 4),
            'type' => fake()->randomElement(['core', 'elective', 'extracurricular']),
            'is_active' => true,
        ];
    }
}
