<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'academic_level_id' => 'required|exists:academic_levels,id',
            'name' => 'required|string',
            'section_head_teacher_id' => 'nullable|exists:teachers,id|unique:sections,section_head_teacher_id',
        ]);

        Section::create([
            'level_id' => $request->academic_level_id,
            'name' => $request->name,
            'section_head_teacher_id' => $request->section_head_teacher_id ?: null,
        ]);

        if($request->classroom_id) {
            // If a classroom is assigned, update the classroom to link to this section
            $section = Section::where('level_id', $request->academic_level_id)
                ->where('name', $request->name)
                ->first();

            if ($section) {
                $classroom = \App\Models\Classroom::find($request->classroom_id);
                if ($classroom) {
                    $classroom->section_id = $section->id;
                    $classroom->is_available = false;
                    $classroom->save();
                }
            }
        }   
        

        return redirect()->back()->with('toast', 'Section created successfully!');
    }
}
