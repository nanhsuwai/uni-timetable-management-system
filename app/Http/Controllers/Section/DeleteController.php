<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\AcademicLevel;

class DeleteController extends Controller
{
    public function __invoke(AcademicLevel $academicLevel, Section $section)
    {
        // Ensure the section belongs to the academic level
        if ($section->level_id !== $academicLevel->id) {
            abort(404);
        }

        $section->delete();

        return to_route('academic_level:all')->with('toast', 'Section deleted successfully!');
    }
}
