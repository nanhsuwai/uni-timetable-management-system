<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlotTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'period_number',
        'status',
        'is_lunch_period',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for ordering by period number
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('period_number');
    }

    /**
     * Get the time slots for this template
     */
    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }
}
