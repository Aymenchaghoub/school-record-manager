<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReportCard>
 */
class ReportCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjectGrades = [];
        $subjects = ['Mathematics', 'English', 'Science', 'History', 'Geography'];
        
        foreach ($subjects as $subject) {
            $subjectGrades[] = [
                'subject_name' => $subject,
                'subject_code' => strtoupper(substr($subject, 0, 3)),
                'credits' => fake()->numberBetween(1, 4),
                'average' => fake()->numberBetween(50, 100),
                'grades_count' => fake()->numberBetween(3, 10),
            ];
        }
        
        $overallAverage = array_sum(array_column($subjectGrades, 'average')) / count($subjectGrades);
        
        return [
            'student_id' => User::factory()->student(),
            'class_id' => ClassModel::factory(),
            'term' => fake()->randomElement(['Term 1', 'Term 2', 'Term 3']),
            'academic_year' => fake()->randomElement(['2023-2024', '2024-2025']),
            'overall_average' => round($overallAverage, 2),
            'total_absences' => fake()->numberBetween(0, 20),
            'justified_absences' => fake()->numberBetween(0, 10),
            'rank_in_class' => fake()->numberBetween(1, 30),
            'total_students' => fake()->numberBetween(20, 35),
            'subject_grades' => $subjectGrades,
            'principal_remarks' => fake()->optional()->paragraph(),
            'teacher_remarks' => fake()->optional()->paragraph(),
            'conduct_grade' => fake()->randomElement(['Excellent', 'Very Good', 'Good', 'Fair', 'Poor']),
            'issue_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'is_final' => fake()->boolean(70),
        ];
    }

    /**
     * Indicate that the report card is final.
     */
    public function final(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_final' => true,
        ]);
    }
}
