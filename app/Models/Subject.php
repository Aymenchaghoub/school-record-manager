<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'credits',
        'type',
        'teacher_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credits' => 'integer',
    ];

    /**
     * Get the primary teacher for this subject
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get classes where this subject is taught
     */
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_subjects', 'subject_id', 'class_id')
            ->withPivot('teacher_id', 'hours_per_week', 'room')
            ->withTimestamps();
    }

    /**
     * Get teachers who teach this subject
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'class_subjects', 'subject_id', 'teacher_id')
            ->withPivot('class_id', 'hours_per_week', 'room')
            ->withTimestamps()
            ->distinct();
    }

    /**
     * Get grades for this subject
     */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'subject_id');
    }

    /**
     * Get absences related to this subject
     */
    public function absences()
    {
        return $this->hasMany(Absence::class, 'subject_id');
    }

    /**
     * Get average grade for this subject
     */
    public function getAverageGrade($studentId = null, $classId = null, $term = null)
    {
        $query = $this->grades();

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($term) {
            $query->where('term', $term);
        }

        $grades = $query->get();

        if ($grades->isEmpty()) {
            return null;
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($grades as $grade) {
            $normalizedScore = ($grade->value / $grade->max_value) * 100;
            $totalWeightedScore += $normalizedScore * $grade->weight;
            $totalWeight += $grade->weight;
        }

        return $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : null;
    }
}
