<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\{
    AcademicYear,
    TimetableEntry,
    TimeSlot
};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        // Get the TimeSlot early to use its properties in validation
        $timeSlot = TimeSlot::find($request->input('time_slot_id'));
        if (!$timeSlot) {
            return back()->withErrors(['time_slot_id' => 'The selected time slot is invalid.'])->withInput();
        }

        // 1️⃣ Consolidated & Optimized Validation
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id', function ($attribute, $value, $fail) {
                $academicYear = AcademicYear::find($value);
                if (!$academicYear || !$academicYear->isActive()) {
                    $fail('The selected academic year must be active.');
                }
            }],
            'semester_id'      => 'required|exists:semesters,id',
            'program_id'       => 'sometimes|exists:academic_programs,id',
            'level_id'         => 'sometimes|exists:academic_levels,id',
            'section_id' => 'nullable|exists:sections,id',
            'classroom_id'     => 'sometimes|exists:classrooms,id',
            'subject_id'       => [
                'required',
                'exists:subjects,id',
                // Rule 1: No duplicate subjects in the same section and time slot.
                Rule::unique('timetable_entries')->where(function ($query) use ($request, $timeSlot) {
                    return $query->where('section_id', $request->input('section_id'))
                        ->where('time_slot_id', $timeSlot->id);
                }),
                // Rule 2 & 3: Subject frequency checks (moved here for efficiency)
                function ($attribute, $value, $fail) use ($request, $timeSlot) {
                    $baseQuery = fn() => TimetableEntry::where('section_id', $request->input('section_id'))
                        ->where('subject_id', $value);

                    if ($baseQuery()->where('day_of_week', $timeSlot->day_of_week)->count() >= 2) {
                        $fail('This subject already appears 2 times today for this section.');
                    }
                    if ($baseQuery()->count() >= 4) {
                        $fail('This subject already appears 4 times this week for this section.');
                    }
                }
            ],
            'teacher_ids'      => 'required|array|min:1',
            'teacher_ids.*'    => 'exists:teachers,id',
            'time_slot_id'     => 'required|exists:time_slots,id',
        ]);

        // 2️⃣ FIX: Check for teacher conflicts with explicit column name
        $teacherConflict = TimetableEntry::where('day_of_week', $timeSlot->day_of_week)
            ->where('time_slot_id', $timeSlot->id)
            ->whereHas('teachers', function ($query) use ($validated) {
                // By specifying 'teachers.id', we remove the ambiguity
                $query->whereIn('teachers.id', $validated['teacher_ids']);
            })
            ->exists();

        if ($teacherConflict) {
            return back()->withErrors(['teacher_ids' => 'One or more selected teachers are already assigned for this period and day.'])->withInput();
        }

        // 3️⃣ Prepare data for creation
        $dataToCreate = $validated + [
            'start_time'  => $timeSlot->start_time,
            'end_time'    => $timeSlot->end_time,
            'day_of_week' => $timeSlot->day_of_week,
        ];

        // 4️⃣ Create timetable entry and attach teachers
        $timetable = TimetableEntry::create($dataToCreate);
        $timetable->teachers()->attach($validated['teacher_ids']);

        return to_route('timetable_entry:all')->with('toast', 'Timetable entry created successfully!');
    }
}
