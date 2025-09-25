<?php

namespace App\Http\Controllers\AcademicLevel;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function toggle(Request $request, AcademicLevel $academicLevel)
    {
        try {
            // Toggle between active and inactive
            $newStatus = $academicLevel->status === 'active' ? 'inactive' : 'active';

            $academicLevel->update(['status' => $newStatus]);

            return to_route('academic_level:all')->with('toast', 'Academic Level status updated successfully!');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }
}
