<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'start_date',
        'end_date',
        'location',
        'class_id',
        'created_by',
        'is_public',
        'color',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_public' => 'boolean',
    ];

    /**
     * Get the class for the event (if class-specific)
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the user who created the event
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for public events
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now())
                     ->orderBy('start_date', 'asc');
    }

    /**
     * Scope for past events
     */
    public function scopePast($query)
    {
        return $query->where('end_date', '<', now())
                     ->orderBy('start_date', 'desc');
    }

    /**
     * Scope for current events
     */
    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
                     ->where(function ($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now());
                     });
    }

    /**
     * Scope for events by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for school-wide events
     */
    public function scopeSchoolWide($query)
    {
        return $query->whereNull('class_id');
    }

    /**
     * Scope for class-specific events
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Get duration in hours
     */
    public function getDurationInHours()
    {
        if (!$this->end_date) {
            return 0;
        }

        return round($this->end_date->diffInMinutes($this->start_date) / 60, 2);
    }

    /**
     * Get duration in days
     */
    public function getDurationInDays()
    {
        if (!$this->end_date) {
            return 1;
        }

        return $this->end_date->diffInDays($this->start_date) + 1;
    }

    /**
     * Check if event is happening now
     */
    public function isHappeningNow()
    {
        $now = now();
        return $this->start_date <= $now && (!$this->end_date || $this->end_date >= $now);
    }

    /**
     * Check if event is in the future
     */
    public function isUpcoming()
    {
        return $this->start_date > now();
    }

    /**
     * Check if event is in the past
     */
    public function isPast()
    {
        return $this->end_date ? $this->end_date < now() : $this->start_date < now();
    }

    /**
     * Get event type label
     */
    public function getTypeLabel()
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Get event type icon
     */
    public function getTypeIcon()
    {
        return match($this->type) {
            'exam' => 'academic-cap',
            'meeting' => 'user-group',
            'holiday' => 'calendar',
            'sports' => 'trophy',
            'cultural' => 'sparkles',
            'parent_meeting' => 'users',
            default => 'flag',
        };
    }
}
