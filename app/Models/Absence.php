<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'subject_id',
        'recorded_by',
        'absence_date',
        'start_time',
        'end_time',
        'is_justified',
        'type',
        'reason',
        'justification',
        'justification_document',
    ];

    protected $casts = [
        'absence_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_justified' => 'boolean',
    ];

    /**
     * Get the student who was absent
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the class for the absence
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the subject for the absence (if applicable)
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the teacher who recorded the absence
     */
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Alias for recordedBy (for backward compatibility)
     */
    public function markedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope for justified absences
     */
    public function scopeJustified($query)
    {
        return $query->where('is_justified', true);
    }

    /**
     * Scope for unjustified absences
     */
    public function scopeUnjustified($query)
    {
        return $query->where('is_justified', false);
    }

    /**
     * Scope for absences by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for absences in date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('absence_date', [$startDate, $endDate]);
    }

    /**
     * Scope for current month absences
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('absence_date', now()->month)
                     ->whereYear('absence_date', now()->year);
    }

    /**
     * Scope to exclude weekend absences (Saturdays and Sundays)
     */
    public function scopeWeekdaysOnly($query)
    {
        return $query->whereRaw('DAYOFWEEK(absence_date) NOT IN (1, 7)'); // 1=Sunday, 7=Saturday
    }

    /**
     * Check if the absence date is a weekend
     */
    public function isWeekend()
    {
        return in_array($this->absence_date->dayOfWeek, [0, 6]); // 0=Sunday, 6=Saturday
    }

    /**
     * Get duration in hours (for partial absences)
     */
    public function getDurationInHours()
    {
        if ($this->type === 'full_day') {
            return 8; // Assuming 8-hour school day
        }

        if ($this->start_time && $this->end_time) {
            $start = \Carbon\Carbon::parse($this->start_time);
            $end = \Carbon\Carbon::parse($this->end_time);
            return round($end->diffInMinutes($start) / 60, 2);
        }

        return 0;
    }

    /**
     * Check if absence can be justified (within time limit)
     */
    public function canBeJustified($daysLimit = 3)
    {
        return $this->absence_date->diffInDays(now()) <= $daysLimit;
    }
}
