<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function toggle(Request $request, Section $section): JsonResponse
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

            $section->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'id' => $section->id,
                    'status' => $section->status
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
