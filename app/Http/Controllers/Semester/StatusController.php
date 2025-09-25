<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function toggle(Request $request, AcademicYear $academicYear, Semester $semester)
    {
        // Ensure the semester belongs to the academic year
        if ($semester->academic_year_id !== $academicYear->id) {
            abort(404);
        }

        try {
            $newStatus = $request->input('status');

            // Validate status
            if (!in_array($newStatus, ['active', 'inactive', 'archived'])) {
                return back()->withErrors(['status' => 'Invalid status value']);
            }

            $semester->update(['status' => $newStatus]);

            return to_route('academic-year:all')->with('toast', 'Semester status updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update status']);
        }
    }
}
