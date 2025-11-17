<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'profile_photo',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is teacher
     */
    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if user is student
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * Check if user is parent
     */
    public function isParent()
    {
        return $this->role === 'parent';
    }

    /**
     * Get the classes for a teacher
     */
    public function teacherClasses()
    {
        return $this->belongsToMany(ClassModel::class, 'class_subjects', 'teacher_id', 'class_id')
            ->withPivot('subject_id', 'hours_per_week', 'room')
            ->withTimestamps();
    }

    /**
     * Get the subjects for a teacher
     */
    public function teacherSubjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subjects', 'teacher_id', 'subject_id')
            ->withPivot('class_id', 'hours_per_week', 'room')
            ->withTimestamps();
    }

    /**
     * Get the class for a student
     */
    public function studentClass()
    {
        return $this->belongsToMany(ClassModel::class, 'student_classes', 'student_id', 'class_id')
            ->withPivot('enrollment_date', 'status')
            ->withTimestamps()
            ->wherePivot('status', 'active')
            ->first();
    }

    /**
     * Get all classes for a student (including historical)
     */
    public function studentClasses()
    {
        return $this->belongsToMany(ClassModel::class, 'student_classes', 'student_id', 'class_id')
            ->withPivot('enrollment_date', 'status')
            ->withTimestamps();
    }

    /**
     * Get grades for a student
     */
    public function studentGrades()
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    /**
     * Get absences for a student
     */
    public function studentAbsences()
    {
        return $this->hasMany(Absence::class, 'student_id');
    }

    /**
     * Get report cards for a student
     */
    public function studentReportCards()
    {
        return $this->hasMany(ReportCard::class, 'student_id');
    }

    /**
     * Get children for a parent
     */
    public function parentChildren()
    {
        return $this->belongsToMany(User::class, 'parent_students', 'parent_id', 'student_id')
            ->withPivot('relationship', 'is_primary_contact')
            ->withTimestamps();
    }

    /**
     * Get parents for a student
     */
    public function studentParents()
    {
        return $this->belongsToMany(User::class, 'parent_students', 'student_id', 'parent_id')
            ->withPivot('relationship', 'is_primary_contact')
            ->withTimestamps();
    }

    /**
     * Get classes where user is responsible teacher
     */
    public function responsibleClasses()
    {
        return $this->hasMany(ClassModel::class, 'responsible_teacher_id');
    }

    /**
     * Get events created by user
     */
    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Get recorded grades by teacher
     */
    public function recordedGrades()
    {
        return $this->hasMany(Grade::class, 'teacher_id');
    }

    /**
     * Get recorded absences by teacher
     */
    public function recordedAbsences()
    {
        return $this->hasMany(Absence::class, 'recorded_by');
    }
}
