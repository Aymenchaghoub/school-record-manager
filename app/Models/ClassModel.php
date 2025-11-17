<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'code',
        'level',
        'section',
        'academic_year',
        'responsible_teacher_id',
        'teacher_id', // Alias for responsible_teacher_id
        'capacity',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    /**
     * Set teacher_id attribute (maps to responsible_teacher_id)
     */
    public function setTeacherIdAttribute($value)
    {
        $this->attributes['responsible_teacher_id'] = $value;
    }

    /**
     * Get teacher_id attribute (maps from responsible_teacher_id)
     */
    public function getTeacherIdAttribute()
    {
        return $this->attributes['responsible_teacher_id'] ?? null;
    }

    /**
     * Get the responsible teacher for the class
     */
    public function responsibleTeacher()
    {
        return $this->belongsTo(User::class, 'responsible_teacher_id');
    }

    /**
     * Alias for responsibleTeacher (for backward compatibility)
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'responsible_teacher_id');
    }

    /**
     * Get students in the class
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'student_classes', 'class_id', 'student_id')
            ->withPivot('enrollment_date', 'status')
            ->withTimestamps()
            ->wherePivot('status', 'active');
    }

    /**
     * Get all students (including inactive) in the class
     */
    public function allStudents()
    {
        return $this->belongsToMany(User::class, 'student_classes', 'class_id', 'student_id')
            ->withPivot('enrollment_date', 'status')
            ->withTimestamps();
    }

    /**
     * Get subjects taught in the class
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subjects', 'class_id', 'subject_id')
            ->withPivot('teacher_id', 'hours_per_week', 'room')
            ->withTimestamps();
    }

    /**
     * Get teachers for the class
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'class_subjects', 'class_id', 'teacher_id')
            ->withPivot('subject_id', 'hours_per_week', 'room')
            ->withTimestamps()
            ->distinct();
    }

    /**
     * Get grades for the class
     */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'class_id');
    }

    /**
     * Get absences for the class
     */
    public function absences()
    {
        return $this->hasMany(Absence::class, 'class_id');
    }

    /**
     * Get report cards for the class
     */
    public function reportCards()
    {
        return $this->hasMany(ReportCard::class, 'class_id');
    }

    /**
     * Get events for the class
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'class_id');
    }

    /**
     * Get the current enrollment count
     */
    public function getCurrentEnrollmentCount()
    {
        return $this->students()->count();
    }

    /**
     * Check if class is full
     */
    public function isFull()
    {
        return $this->getCurrentEnrollmentCount() >= $this->capacity;
    }

    /**
     * Get available seats
     */
    public function getAvailableSeats()
    {
        return max(0, $this->capacity - $this->getCurrentEnrollmentCount());
    }
}
