<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassModel>
 */
class ClassModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = fake()->randomElement(['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6']);
        $section = fake()->randomElement(['A', 'B', 'C']);
        
        return [
            'name' => "$level - Section $section",
            'code' => str_replace(' ', '', $level) . $section . '-' . fake()->unique()->numerify('###'),
            'level' => $level,
            'section' => $section,
            'academic_year' => fake()->randomElement(['2023-2024', '2024-2025']),
            'responsible_teacher_id' => User::factory()->teacher(),
            'capacity' => fake()->numberBetween(20, 35),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the class is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
