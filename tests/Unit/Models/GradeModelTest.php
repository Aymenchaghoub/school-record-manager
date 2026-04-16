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
}
