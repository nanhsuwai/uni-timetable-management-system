<?php

namespace App\Http\Controllers\AcademicLevel;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:academic_programs,id',
            'name' => 'required|string',
        ]);

        // Get active academic year
        $activeAcademicYear = AcademicYear::getActive();

        // Validate that the program belongs to the active academic year
        if ($activeAcademicYear) {
            $program = \App\Models\AcademicProgram::find($request->program_id);
            if (!$program || $program->academic_year_id != $activeAcademicYear->id) {
                return back()->withErrors(['program_id' => 'The selected program does not belong to the active academic year.']);
            }
        }

        AcademicLevel::create([
            'program_id' => $request->program_id,
            'name' => $request->name,
        ]);

        return to_route('academic_level:all')->with('toast', 'Academic Level created successfully!');
    }
}
