<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Absence>
 */
class AbsenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['full_day', 'partial', 'late_arrival', 'early_departure']);
        $startTime = null;
        $endTime = null;
        
        if ($type === 'partial' || $type === 'late_arrival' || $type === 'early_departure') {
            $startTime = fake()->time('H:i:00');
            $endTime = fake()->time('H:i:00');
        }
        
        return [
            'student_id' => User::factory()->student(),
            'class_id' => ClassModel::factory(),
            'subject_id' => fake()->optional()->randomElement([null, Subject::factory()]),
            'recorded_by' => User::factory()->teacher(),
            'absence_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_justified' => fake()->boolean(30),
            'type' => $type,
            'reason' => fake()->randomElement(['Sick', 'Family emergency', 'Medical appointment', 'Other']),
            'justification' => fake()->optional()->sentence(),
            'justification_document' => null,
        ];
    }

    /**
     * Indicate that the absence is justified.
     */
    public function justified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_justified' => true,
            'justification' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the absence is unjustified.
     */
    public function unjustified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_justified' => false,
            'justification' => null,
        ]);
    }
}
