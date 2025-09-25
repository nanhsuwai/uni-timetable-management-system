<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\AcademicLevel;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request, AcademicLevel $academicLevel)
    {
        $request->validate([
            'name' => 'required|string',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        Section::create([
            'level_id' => $academicLevel->id,
            'name' => $request->name,
        ]);
        if ($request->classroom_id) {
            $classroom = \App\Models\Classroom::find($request->classroom_id);
            if ($classroom) {
                $classroom->section_id = Section::latest()->first()->id;
                $classroom->level_id = $academicLevel->id;
                $classroom->save();
            }
        }

        return to_route('academic_level:all')->with('toast', 'Section created successfully!');
    }
}
