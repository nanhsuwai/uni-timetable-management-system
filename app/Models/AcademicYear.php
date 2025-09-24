<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        // When setting an academic year as active, deactivate all others
        static::updating(function ($academicYear) {
            if ($academicYear->status === 'active' && $academicYear->isDirty('status')) {
                static::where('id', '!=', $academicYear->id)
                      ->where('status', 'active')
                      ->update(['status' => 'inactive']);
            }
        });

        static::creating(function ($academicYear) {
            if ($academicYear->status === 'active') {
                // If creating as active, deactivate all existing active ones
                static::where('status', 'active')->update(['status' => 'inactive']);
            }
        });
    }

    /**
     * Get the status as numeric value for frontend compatibility
     */
    public function getStatusNumericAttribute()
    {
        return $this->status === 'active' ? 1 : 0;
    }

    /**
     * Scope to get only active academic years
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the currently active academic year
     */
    public static function getActive()
    {
        return static::active()->first();
    }

    /**
     * Set this academic year as active
     */
    public function setAsActive()
    {
        // Deactivate all other academic years
        static::where('status', 'active')->update(['status' => 'inactive']);

        // Set this one as active
        $this->update(['status' => 'active']);

        return $this;
    }

    /**
     * Check if this academic year is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function academicPrograms()
    {
        return $this->hasMany(AcademicProgram::class);
    }
}
