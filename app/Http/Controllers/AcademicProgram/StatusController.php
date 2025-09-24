<?php

namespace App\Http\Controllers\AcademicProgram;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function toggle(Request $request, AcademicProgram $academicProgram): JsonResponse
    {
        try {
            $newStatus = $request->input('status');
            
            // Validate status
            if (!in_array($newStatus, ['active', 'inactive', 'archived'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status value'
                ], 400);
            }

            $academicProgram->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'id' => $academicProgram->id,
                    'status' => $academicProgram->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }
}
