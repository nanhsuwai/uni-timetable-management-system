<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\AcademicLevel;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function toggle(Request $request, AcademicLevel $academicLevel, Section $section)
    {
        // Ensure the section belongs to the academic level
        if ($section->level_id !== $academicLevel->id) {
            abort(404);
        }

        try {
            $newStatus = $request->input('status');

            // Validate status
            if (!in_array($newStatus, ['active', 'inactive', 'archived'])) {
                return back()->withErrors(['status' => 'Invalid status value']);
            }

            $section->update(['status' => $newStatus]);

            return to_route('academic_level:all')->with('toast', 'Section status updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update status']);
        }
    }
}
