<?php

namespace App\Http\Controllers\TimeSlotTemplate;

use App\Http\Controllers\Controller;
use App\Models\TimeSlotTemplate;
use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function __invoke(Request $request, TimeSlotTemplate $template)
    {
        try {
            $template->delete();

            return response()->json([
                'message' => 'Time slot template deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete time slot template',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
