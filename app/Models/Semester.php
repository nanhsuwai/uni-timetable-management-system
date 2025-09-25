<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicYear;
use App\Models\AcademicProgram;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }



    /**
     * Get the validation rules for semester creation/update
     */
    public static function getValidationRules()
    {
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|in:First Semester,Second Semester',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }



    /**
     * Get the status as numeric value for frontend compatibility
     */
    public function getStatusNumericAttribute()
    {
        return $this->status === 'active' ? 1 : 0;
    }

    /**
     * Check if this semester is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Scope to get only active semesters
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
