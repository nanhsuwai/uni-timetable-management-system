<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Semester $semester)
    {
        $request->validate([
            'program_id' => 'required|exists:academic_programs,id',
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $semester->update([
            'program_id' => $request->program_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return to_route('semester:all')->with('toast', 'Semester updated successfully!');
    }
}
