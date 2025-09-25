<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Semester $semester)
    {
        $request->validate(Semester::getValidationRules());

        // Create semester instance for validation
        $semesterToValidate = new Semester($request->all());

        // Additional validation to ensure program belongs to academic year
        if (!$semesterToValidate->validateProgramBelongsToAcademicYear()) {
            return back()->withErrors(['program_id' => 'The selected program does not belong to the selected academic year.']);
        }

        $semester->update([
            'academic_year_id' => $request->academic_year_id,
            'program_id' => $request->program_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return to_route('semester:all')->with('toast', 'Semester updated successfully!');
    }
}
