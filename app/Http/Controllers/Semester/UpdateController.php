<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, AcademicYear $academicYear, Semester $semester)
    {
        // Ensure the semester belongs to the academic year
        if ($semester->academic_year_id !== $academicYear->id) {
            abort(404);
        }

        $rules = Semester::getValidationRules();
        unset($rules['academic_year_id']); // Remove academic_year_id from validation
        $request->validate($rules);

        if ($request->start_date < $academicYear->start_date || $request->end_date > $academicYear->end_date) {
            return back()->withErrors(['start_date' => 'Semester dates must be within the academic year dates.']);
        }

        try {
            $semester->update([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->withErrors(['name' => 'This semester already exists for the selected academic year.']);
            }
            throw $e;
        }

        return to_route('academic-year:all')->with('toast', 'Semester updated successfully!');
    }
}
