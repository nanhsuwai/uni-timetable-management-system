<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicProgram;

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

    /**
     * Get the validation rules for academic level creation/update
     */
    public static function getValidationRules()
    {
        return [
            'program_id' => 'required|exists:academic_programs,id',
            'name' => 'required|in:First Year,Second Year,Third Year,Fourth Year,Fifth Year,Master',
        ];
    }

    /**
     * Validate if the level is allowed for the program's type
     * Assume: First Year for all programs, higher levels only for CS/CT programs
     */
    public function validateLevelForProgramType()
    {
        $program = $this->academicProgram;
        if (!$program) {
            return false;
        }

        $levelNames = ['First Year', 'Second Year', 'Third Year', 'Fourth Year', 'Fifth Year', 'Master'];
        $levelIndex = array_search($this->name, $levelNames);

        if ($this->name === 'First Year') {
            return true; // First Year allowed for all
        }

        // Higher levels only for CS or CT programs
        return in_array($program->program_type, ['CS', 'CT']);
    }

    /**
     * Get allowed levels for a given program type
     */
    public static function getAllowedLevelsForProgramType($programType)
    {
        $allLevels = ['First Year', 'Second Year', 'Third Year', 'Fourth Year', 'Fifth Year', 'Master'];
        if ($programType === 'general' || empty($programType)) {
            return ['First Year']; // Only First Year for general/single program
        }
        if (in_array($programType, ['CS', 'CT'])) {
            return $allLevels; // Full levels for CS/CT
        }
        return ['First Year']; // Default to First Year
    }
}
