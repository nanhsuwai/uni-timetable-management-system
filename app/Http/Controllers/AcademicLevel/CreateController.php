<?php

namespace App\Http\Controllers\AcademicLevel;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\AcademicYear;
use App\Models\AcademicProgram;

use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        // Validate including semester
        $request->validate(AcademicLevel::getValidationRules());
        $activeAcademicYear = AcademicYear::getActiveYears()->first();
        $academicPrograms = AcademicProgram::where('academic_year_id', $activeAcademicYear?->id)
            ->orderBy('name')
            ->get();
        // Check if level already exists for the given program, semester, and name
        $exists = AcademicLevel::where('program_id', $request->program_id)
            ->where('name', $request->name)
            ->where('semester', $request->semester)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'This level with the selected semester already exists for this program.',
            ])->withInput();
        }

        // Create new level
        $level = AcademicLevel::create([
            'program_id' => $request->program_id,
            'name'       => $request->name,
            'semester'   => $request->semester,
        ]);

        return to_route('academic_level:all')
            ->with('toast', 'Academic Level created successfully!');
    }
}
