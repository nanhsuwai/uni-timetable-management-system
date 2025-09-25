<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\AcademicYear;

class DeleteController extends Controller
{
    public function __invoke(AcademicYear $academicYear, Semester $semester)
    {
        // Ensure the semester belongs to the academic year
        if ($semester->academic_year_id !== $academicYear->id) {
            abort(404);
        }

        $semester->delete();

        return to_route('academic-year:all')->with('toast', 'Semester deleted successfully!');
    }
}
