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
        'program_id',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    /**
     * Get the validation rules for semester creation/update
     */
    public static function getValidationRules()
    {
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'program_id' => 'required|exists:academic_programs,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }

    /**
     * Check if the program belongs to the academic year
     */
    public function validateProgramBelongsToAcademicYear()
    {
        $academicProgram = $this->academicProgram;
        return $academicProgram && $academicProgram->academic_year_id == $this->academic_year_id;
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
