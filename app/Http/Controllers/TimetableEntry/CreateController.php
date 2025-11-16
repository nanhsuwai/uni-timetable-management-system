<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\{
    AcademicYear,
    TimetableEntry,
    TimeSlot,
    Subject,
    AcademicLevel
};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        // ================================
        // 1. VALIDATION
        // ================================
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id'),
                function ($attribute, $value, $fail) {
                    $year = AcademicYear::find($value);
                    if (!$year || !$year->isActive()) {
                        $fail("The selected academic year must be active.");
                    }
                }
            ],
            'semester_id'      => ['required', Rule::exists('semesters', 'id')],
            'program_id'       => ['required', Rule::exists('academic_programs', 'id')],
            'level_id'         => ['required', Rule::exists('academic_levels', 'id')],
            'section_id'       => ['required', Rule::exists('sections', 'id')],
            'classroom_id'     => ['required', Rule::exists('classrooms', 'id')],
            'subject_id'       => ['required', Rule::exists('subjects', 'id')],
            'teacher_ids'      => ['required', 'array', 'min:1'],
            'teacher_ids.*'    => [Rule::exists('teachers', 'id')],
            'time_slot_ids'    => ['required', 'array', 'min:1', 'max:2'],
            'time_slot_ids.*'  => ['required', Rule::exists('time_slots', 'id')],
        ]);

        $sectionId = $validated['section_id'];
        $subjectId = $validated['subject_id'];

        // Fetch all selected time slots
        $timeSlots = TimeSlot::whereIn('id', $validated['time_slot_ids'])->get();

        if ($timeSlots->count() !== count($validated['time_slot_ids'])) {
            return back()->withErrors(['time_slot_ids' => 'One or more selected time slots are invalid.'])->withInput();
        }

        // ================================
        // 2. LOOP THROUGH EACH SLOT
        // ================================
        foreach ($timeSlots as $slot) {

            // --- DAILY SUBJECT LIMIT (max 2 per day) ---
            $dailyCount = TimetableEntry::where('section_id', $sectionId)
                ->where('subject_id', $subjectId)
                ->where('day_of_week', $slot->day_of_week)
                ->count();

            if ($dailyCount >= 2) {
                return back()->withErrors([
                    'subject_id' => "This subject already appears 2 times on {$slot->day_of_week}."
                ])->withInput();
            }

            // --- WEEKLY SUBJECT LIMIT (max 4 per week / section / semester / academic year) ---
            $weeklyCount = TimetableEntry::where('section_id', $sectionId)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('semester_id', $validated['semester_id'])
                ->count();

            if ($weeklyCount >= 4) {
                return back()->withErrors([
                    'subject_id' => "This subject already appears 4 times this week for this section."
                ])->withInput();
            }

            // --- DUPLICATE TIME SLOT CHECK ---
            $duplicate = TimetableEntry::where('section_id', $sectionId)
                ->where('time_slot_id', $slot->id)
                ->where('day_of_week', $slot->day_of_week)
                ->exists();

            if ($duplicate) {
                return back()->withErrors([
                    'time_slot_ids' => "Time slot {$slot->id} is already assigned for this section."
                ])->withInput();
            }

            // --- TEACHER CONFLICT CHECK ---
            $teacherConflict = TimetableEntry::where('day_of_week', $slot->day_of_week)
                ->where('time_slot_id', $slot->id)
                ->whereHas('teachers', function ($query) use ($validated) {
                    $query->whereIn('teachers.id', $validated['teacher_ids']);
                })
                ->exists();

            // CST exception rule
            $inputSubject = Subject::find($validated['subject_id'])->code;
            $cstSubjects  = Subject::CSTSubject()->pluck("code")->toArray();
            $isFirstYear  = AcademicLevel::isFirstYear($validated['level_id']);
            if (in_array($inputSubject, $cstSubjects) && !$isFirstYear) {
                $teacherConflict = false;
            }

            if ($teacherConflict) {
                return back()->withErrors([
                    'teacher_ids' => "One or more selected teachers are already assigned at this time."
                ])->withInput();
            }

            // --- CREATE TIMETABLE ENTRY ---
            $entry = TimetableEntry::create([
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id'      => $validated['semester_id'],
                'program_id'       => $validated['program_id'],
                'level_id'         => $validated['level_id'],
                'section_id'       => $sectionId,
                'classroom_id'     => $validated['classroom_id'],
                'subject_id'       => $subjectId,
                'time_slot_id'     => $slot->id,
                'day_of_week'      => $slot->day_of_week,
                'start_time'       => $slot->start_time,
                'end_time'         => $slot->end_time,
            ]);

            // Attach teachers
            $entry->teachers()->attach($validated['teacher_ids']);
        }

        return to_route('timetable_entry:all')->with('toast', 'Timetable entries created successfully!');
    }
}
