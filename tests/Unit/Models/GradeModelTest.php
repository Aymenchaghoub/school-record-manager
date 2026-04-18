<?php

namespace Tests\Unit\Models;

use App\Models\Grade;
use Tests\TestCase;

class GradeModelTest extends TestCase
{
    public function test_percentage_calculation_is_correct(): void
    {
        $grade = Grade::factory()->create([
            'value' => 17,
            'max_value' => 20,
        ]);

        $this->assertSame(85.0, $grade->getPercentage());
    }

    public function test_letter_grade_is_computed_from_percentage(): void
    {
        $grade = Grade::factory()->create([
            'value' => 12,
            'max_value' => 20,
        ]);

        $this->assertSame('C-', $grade->getLetterGrade());
    }

    public function test_percentage_returns_zero_when_max_value_is_zero(): void
    {
        $grade = Grade::factory()->create([
            'value' => 10,
            'max_value' => 0,
        ]);

        $this->assertSame(0.0, (float) $grade->getPercentage());
    }

    public function test_grade_color_is_green_for_high_scores(): void
    {
        $grade = Grade::factory()->create([
            'value' => 18,
            'max_value' => 20,
        ]);

        $this->assertSame('text-green-600', $grade->getGradeColor());
    }
}
