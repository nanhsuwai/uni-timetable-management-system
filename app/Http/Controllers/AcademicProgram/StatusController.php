<?php

namespace App\Http\Controllers\AcademicProgram;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function toggle(Request $request, AcademicProgram $academicProgram)
    {
        try {
            // Toggle between active and inactive
            $newStatus = $academicProgram->status === 'active' ? 'inactive' : 'active';

            $academicProgram->update(['status' => $newStatus]);

            if ($newStatus === 'inactive') {
                // Cascade inactive status to related academic levels
                \App\Models\AcademicLevel::where('program_id', $academicProgram->id)->update(['status' => 'inactive']);
            }

            return to_route('academic_program:all')->with('toast', 'Academic Program status updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to update status'])->withInput();
        }
    }
}
