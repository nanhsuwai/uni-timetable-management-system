<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\AcademicLevel;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, AcademicLevel $academicLevel, Section $section)
    {
        // Ensure the section belongs to the academic level
        if ($section->level_id !== $academicLevel->id) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string',
        ]);

        $section->update([
            'name' => $request->name,
        ]);

        return to_route('academic_level:all')->with('toast', 'Section updated successfully!');
    }
}
