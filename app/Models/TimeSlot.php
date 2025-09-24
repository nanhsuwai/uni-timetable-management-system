<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicYear;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'academic_year_id',
        'time_slot_template_id',
        'day_of_week',
        'start_time',
        'end_time',
        'status',
        'is_lunch_period'
    ];

    /**
     * Cast attributes to specific types
     */
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Get the status as numeric value for frontend compatibility
     */
    public function getStatusNumericAttribute()
    {
        return $this->status === 'active' ? 1 : 0;
    }

    /**
     * Get the academic year that owns the time slot
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the time slot template that owns the time slot
     */
    public function timeSlotTemplate()
    {
        return $this->belongsTo(TimeSlotTemplate::class);
    }

    /**
     * Get the timetable entries for this time slot
     */
    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }
}
