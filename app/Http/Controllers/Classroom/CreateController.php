<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'room_no' => 'required|string|max:255',
            // section_id is optional now
        ]);

        Classroom::create([
            'room_no' => $request->room_no,
            // optionally, section_id can be null
            'section_id' => $request->section_id ?? null,
        ]);

        return to_route('classroom:all')->with('toast', 'Classroom created successfully!');
    }
}
