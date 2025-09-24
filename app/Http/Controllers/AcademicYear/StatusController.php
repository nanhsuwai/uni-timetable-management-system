<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function toggle(Request $request, AcademicYear $academicYear)
    {
        try {
            // Toggle between active and inactive
            $newStatus = $academicYear->status === 'active' ? 'inactive' : 'active';

            $academicYear->update(['status' => $newStatus]);

             return to_route('academic-year:all')->with('toast', 'Academic Year status updated successfully!');


        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }
}
