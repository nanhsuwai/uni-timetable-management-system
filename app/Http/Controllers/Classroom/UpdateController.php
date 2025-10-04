<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Classroom $classroom)
    {
        // Validation
        $request->validate([
            /* 'section_id' => 'required|exists:sections,id', */
            'room_no' => 'required|string|max:50',
        ]);

        // Update classroom
        $classroom->update([
            /* 'section_id' => $request->section_id, */
            'room_no' => $request->room_no,
        ]);

        return to_route('classroom:all')->with('toast', 'Classroom updated successfully!');
    }
}
