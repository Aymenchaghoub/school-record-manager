<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'class_id',
        'teacher_id',
        'value',
        'max_value',
        'type',
        'title',
        'grade_date',
        'term',
        'weight',
        'comment',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'weight' => 'decimal:2',
        'grade_date' => 'date',
    ];

    /**
     * Get the student who received the grade
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the subject for the grade
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the class for the grade
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the teacher who gave the grade
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the percentage score
     */
    public function getPercentage()
    {
        if ($this->max_value == 0) {
            return 0;
        }
        return round(($this->value / $this->max_value) * 100, 2);
    }

    /**
     * Get letter grade
     */
    public function getLetterGrade()
    {
        $percentage = $this->getPercentage();

        if ($percentage >= 90) return 'A+';
        if ($percentage >= 85) return 'A';
        if ($percentage >= 80) return 'A-';
        if ($percentage >= 77) return 'B+';
        if ($percentage >= 73) return 'B';
        if ($percentage >= 70) return 'B-';
        if ($percentage >= 67) return 'C+';
        if ($percentage >= 63) return 'C';
        if ($percentage >= 60) return 'C-';
        if ($percentage >= 57) return 'D+';
        if ($percentage >= 53) return 'D';
        if ($percentage >= 50) return 'D-';
        return 'F';
    }

    /**
     * Get grade color for UI display
     */
    public function getGradeColor()
    {
        $percentage = $this->getPercentage();

        if ($percentage >= 85) return 'text-green-600';
        if ($percentage >= 70) return 'text-blue-600';
        if ($percentage >= 60) return 'text-yellow-600';
        if ($percentage >= 50) return 'text-orange-600';
        return 'text-red-600';
    }

    /**
     * Scope for grades by term
     */
    public function scopeByTerm($query, $term)
    {
        return $query->where('term', $term);
    }

    /**
     * Scope for grades by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for recent grades
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('grade_date', '>=', now()->subDays($days));
    }
}
