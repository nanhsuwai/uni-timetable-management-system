<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\AcademicLevel;
use App\Models\Classroom;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Section $section)
    {
        $request->validate([
            'academic_level_id' => 'required|exists:academic_levels,id',
            'name' => 'required|string',
            'section_head_teacher_id' => 'nullable|exists:teachers,id|unique:sections,section_head_teacher_id,' . $section->id,
            'classroom_id' => 'required|exists:classrooms,id',
        ]);
        try {
            $isavailableClassroom = Classroom::where('id', $request->classroom_id)->where('is_available', false)->first();
            if ($isavailableClassroom && $section->classroom?->id != $isavailableClassroom->id) {
                // dd($isavailableClassroom);
                throw new \Exception('This classroom is already assigned to another section.');
            }
            $isHasHeaderTeacher = Section::where('section_head_teacher_id', $request->section_head_teacher_id)->where('id', '!=', $section->id)->first();
            if ($isHasHeaderTeacher) {
                throw new \Exception('This teacher is already assigned as a section head to another section.');
            }
            $section->update([
                'level_id' => $request->academic_level_id,
                'name' => $request->name,
                'section_head_teacher_id' => $request->section_head_teacher_id ?: null,
            ]);

            $section->load('classroom');
    // dd($section);
            if ($request->classroom_id) {
                // If a classroom is assigned, update the classroom to link to this section
                $classroom = \App\Models\Classroom::find($request->classroom_id);
                if ($classroom) {
                    if ($classroom->section_id && $classroom->section_id != $section->id) {
                        throw new \Exception('This classroom is already assigned to another section.');
                    }
                    $classroom->section_id = $section->id;
                    $classroom->level_id = $request->academic_level_id;
                    $classroom->is_available = false;
                    $classroom->save();
                }
                // If the section was previously assigned to a different classroom, mark that classroom as available
                $oldClassroom = Classroom::where('section_id', $section->id)->first();
                
                if ($oldClassroom && $oldClassroom->id != $request->classroom_id) {
                    $oldClassroom = $section->classroom;
                    $oldClassroom->section_id = null;
                    $oldClassroom->is_available = true;
                    $oldClassroom->save();
                }
               
            }
        } catch (\Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()]);
        }

        return redirect()->back()->with('toast', 'Section updated successfully!');
    }
}
