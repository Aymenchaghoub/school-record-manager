<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Subject;
use App\Models\ClassModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxValue = fake()->randomElement([10, 20, 50, 100]);
        $value = fake()->numberBetween($maxValue * 0.4, $maxValue);
        
        return [
            'student_id' => User::factory()->student(),
            'subject_id' => Subject::factory(),
            'class_id' => ClassModel::factory(),
            'teacher_id' => User::factory()->teacher(),
            'value' => $value,
            'max_value' => $maxValue,
            'type' => fake()->randomElement(['exam', 'quiz', 'assignment', 'project', 'participation', 'midterm', 'final']),
            'title' => fake()->words(3, true),
            'grade_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'term' => fake()->randomElement(['Term 1', 'Term 2', 'Term 3']),
            'weight' => fake()->numberBetween(1, 3),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
