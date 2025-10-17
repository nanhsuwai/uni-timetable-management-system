<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateController extends Controller
{
    /**
     * Update the specified section in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, Section $section)
    {
        // 1️⃣ Enhanced Validation
        $validatedData = $request->validate([
            'academic_level_id' => ['required', 'exists:academic_levels,id'],
            // ✅ CHANGED: 'required' is now 'nullable' to allow optional section names.
            'name'                => ['nullable', 'string', 'max:255'],
            'section_head_teacher_id' => [
                'nullable',
                'exists:teachers,id',
                Rule::unique('sections')->ignore($section->id),
            ],
            'classroom_id' => [
                'required',
                'exists:classrooms,id',
                function ($attribute, $value, $fail) use ($section) {
                    $classroom = Classroom::find($value);
                    if ($classroom->section_id && $classroom->section_id !== $section->id) {
                        $fail('This classroom is already assigned to another section.');
                    }
                },
            ],
        ]);

        try {
            // 2️⃣ Use a Database Transaction for data integrity
            DB::transaction(function () use ($validatedData, $section) {
                $oldClassroomId = $section->classroom?->id;
                $newClassroomId = $validatedData['classroom_id'];

                // 3️⃣ Update the Section model
                $section->update([
                    'level_id'                => $validatedData['academic_level_id'],
                    'name'                    => $validatedData['name'], // This will correctly pass null if the name is empty
                    'section_head_teacher_id' => $validatedData['section_head_teacher_id'] ?? null,
                ]);

                // 4️⃣ Efficiently handle classroom changes
                if ($oldClassroomId !== $newClassroomId) {
                    if ($oldClassroomId) {
                        Classroom::where('id', $oldClassroomId)->update([
                            'section_id'   => null,
                            'is_available' => true,
                        ]);
                    }

                    Classroom::where('id', $newClassroomId)->update([
                        'section_id'   => $section->id,
                        'level_id'     => $validatedData['academic_level_id'],
                        'is_available' => false,
                    ]);
                }
            });
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'An error occurred while updating the section. ' . $e->getMessage()]);
        }

        return redirect()->back()->with('toast', 'Section updated successfully!');
    }
}