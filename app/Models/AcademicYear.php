<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AcademicYear extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Use 'status' as a boolean (1 or 0) for simplicity if possible,
     * but keeping the string 'active'/'inactive' based on your provided schema.
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status', // Must be fillable for mass updates
    ];

    /**
     * No more boot() methods needed!
     * The logic to deactivate others is removed as you now allow multiple active years.
     * The business logic is now handled in the Controller via a batch update.
     */
    // protected static function boot() { /* Removed */ }

    // --- Accessors and Mutators (Casting for Efficiency) ---

    /**
     * Cast the 'status' attribute to a boolean for cleaner usage in code.
     * This assumes the database stores 'active' and 'inactive' (strings).
     */
    protected $casts = [
        // Automatically convert status to a boolean when reading from the database
        'status' => 'string', // Keeping 'string' as per your original code
    ];

    /**
     * Get the status as numeric value for frontend compatibility.
     * Renamed to follow Laravel's convention for casting an attribute.
     * @return int
     */
    public function getStatusNumericAttribute()
    {
        // Check if the string value is 'active'
        return $this->status === 'active' ? 1 : 0;
    }

    // --- Query Scopes and Static Methods ---

    /**
     * Scope to get only active academic years.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the currently active academic years (plural).
     * Renamed to better reflect the new multiple-active requirement.
     */
    public static function getActiveYears()
    {
        return static::active()->get();
    }
    
    /**
     * Check if this academic year is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // --- Relationships ---

    public function academicPrograms()
    {
        return $this->hasMany(AcademicProgram::class);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
}