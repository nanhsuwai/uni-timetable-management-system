<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicProgram;
use App\Models\Section;

class AcademicLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'status',
    ];

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'level_id');
    }

    /**
     * Get the validation rules for academic level creation/update
     */
    public static function getValidationRules()
    {
        return [
            'program_id' => 'required|exists:academic_programs,id',
            'name' => 'required|in:First Year,Second Year,Third Year,Fourth Year,Fifth Year,Coursework,Thesis'
        ];
    }

    /**
     * Validate if the level is allowed for the program's type
     * Assume: First Year for all programs, higher levels only for Computer Science/Technology/Master
     */
    public function validateLevelForProgramType()
    {
        $program = $this->academicProgram;
        if (!$program) {
            return false;
        }

        $levelNames = ['First Year', 'Second Year', 'Third Year', 'Fourth Year', 'Fifth Year', 'Coursework', 'Thesis'];
        $levelIndex = array_search($this->name, $levelNames);

        if ($this->name === 'First Year') {
            return true; // First Year allowed for all
        }

        // Higher levels only for Computer Science, Computer Technology, or Master programs
        return in_array($program->program_type, ['CS', 'CT', 'Master']);
    }

    /**
     * Get allowed levels for a given program type
     */
    public static function getAllowedLevelsForProgramType($programType)
    {
        $allLevels = ['First Year', 'Second Year', 'Third Year', 'Fourth Year', 'Fifth Year', 'Master'];
        if ($programType === 'CST' || empty($programType)) {
            return ['First Year']; // Only First Year for Computer Foundation/general
        }
        if (in_array($programType, ['CS', 'CT', 'Master'])) {
            return $allLevels; // Full lels for specialized programs
        }
        return ['First Year']; // Default to First Year
    }
    public function scopeIsFirstYear($query, $levelId)
    {
        return $query->where('id', $levelId)
            ->where('name', 'First Year') // or whatever column indicates first year
            ->exists(); // Returns boolean
    }
}
