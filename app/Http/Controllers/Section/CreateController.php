<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Classroom;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        // 1️⃣ Validate input
        $request->validate([
            'academic_level_id'         => 'required|exists:academic_levels,id',
            'name'                      => 'nullable|string|max:255', // optional section name
            'section_head_teacher_id'   => 'nullable|exists:teachers,id|unique:sections,section_head_teacher_id',
            'classroom_id'              => 'nullable|exists:classrooms,id',
        ]);

        // 2️⃣ Create section
        $section = Section::create([
            'level_id'                  => $request->academic_level_id,
            'name'                      => $request->name ?: null, // allow null
            'section_head_teacher_id'   => $request->section_head_teacher_id ?: null,
        ]);

        // 3️⃣ Link classroom if provided
        if ($request->classroom_id) {
            $classroom = Classroom::find($request->classroom_id);
            if ($classroom) {
                $classroom->section_id = $section->id;
                $classroom->is_available = false;
                $classroom->save();
            }
        }

        return redirect()->back()->with('toast', 'Section created successfully!');
    }
}
