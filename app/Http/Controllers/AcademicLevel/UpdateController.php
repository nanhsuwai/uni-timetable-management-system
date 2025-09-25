<?php

namespace App\Http\Controllers\AcademicLevel;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, AcademicLevel $academicLevel)
    {
        $request->validate(AcademicLevel::getValidationRules());

        // Get active academic year
        $activeAcademicYear = AcademicYear::getActive();

        // Validate that the program belongs to the active academic year
        $program = \App\Models\AcademicProgram::find($request->program_id);
        if (!$program || ($activeAcademicYear && $program->academic_year_id != $activeAcademicYear->id)) {
            return back()->withErrors(['program_id' => 'The selected program does not belong to the active academic year.']);
        }

        // Temporarily set values to validate
        $academicLevel->program_id = $request->program_id;
        $academicLevel->name = $request->name;

        if (!$academicLevel->validateLevelForProgramType()) {
            return back()->withErrors(['name' => 'This level is not allowed for the selected program type.']);
        }

        try {
            $academicLevel->save();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->withErrors(['name' => 'This level already exists for the selected program.']);
            }
            throw $e;
        }

        return to_route('academic_level:all')->with('toast', 'Academic Level updated successfully!');
    }
}
