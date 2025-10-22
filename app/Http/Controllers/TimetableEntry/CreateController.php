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
use Illuminate\Support\Facades\DB;

class CreateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // Early lookup for TimeSlot, essential for validation closures
        $timeSlot = TimeSlot::find($request->input('time_slot_id'));

        if (!$timeSlot) {
            return back()->withErrors(['time_slot_id' => 'The selected time slot is invalid.'])->withInput();
        }

        // --- 1. Consolidated Validation ---
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                // Use DB existence check
                Rule::exists('academic_years', 'id'),
                // Check if the Academic Year is active using a custom closure
                function ($attribute, $value, $fail) {
                    // Optimized: Only query for existence and status if the exists check passed.
                    $academicYear = AcademicYear::find($value);
                    if (!$academicYear || !$academicYear->isActive()) {
                        $fail('The selected academic year must be active.');
                    }
                },
            ],
            'semester_id'    => ['required', Rule::exists('semesters', 'id')],
            'program_id'     => ['sometimes', Rule::exists('academic_programs', 'id')],
            'level_id'       => ['sometimes', Rule::exists('academic_levels', 'id')],
            'section_id'     => ['nullable', Rule::exists('sections', 'id')],
            'classroom_id'   => ['sometimes', Rule::exists('classrooms', 'id')],
            'subject_id'     => [
                'required',
                Rule::exists('subjects', 'id'),
                // Rule A: No duplicate subjects in the same section and time slot (unique constraint)
                Rule::unique('timetable_entries')->where(
                    fn($query) => $query
                        ->where('section_id', $request->input('section_id'))
                        ->where('time_slot_id', $timeSlot->id)
                )->ignore($request->route('timetable_entry'), 'id'), // Ignore current entry if used for update

                // Rule B & C: Subject frequency checks (Max 2 per day, max 4 per week)
                function ($attribute, $value, $fail) use ($request, $timeSlot) {
                    $sectionId = $request->input('section_id');

                    // Base query for frequency checks
                    $baseQuery = TimetableEntry::where('section_id', $sectionId)
                        ->where('subject_id', $value);

                    // Max 2 per day check
                    if ($baseQuery->clone()->where('day_of_week', $timeSlot->day_of_week)->count() >= 2) {
                        $fail('This subject already appears 2 times today for this section.');
                    }

                    // Max 4 per week check
                    if ($baseQuery->count() >= 4) {
                        $fail('This subject already appears 4 times this week for this section.');
                    }
                }
            ],
            'teacher_ids'    => ['required', 'array', 'min:1'],
            'teacher_ids.*'  => [Rule::exists('teachers', 'id')],
            'time_slot_id'   => ['required', Rule::exists('time_slots', 'id')],
        ]);

        // --- Additional Check: Prevent Duplicate Period for the Same Section ---
        $duplicatePeriod = TimetableEntry::where('section_id', $request->input('section_id'))
            ->where('time_slot_id', $timeSlot->id)
            ->where('day_of_week', $timeSlot->day_of_week)
            ->exists();

        if ($duplicatePeriod) {
            return back()->withErrors([
                'time_slot_id' => 'This period is already assigned for this section. Please choose another time slot.',
            ])->withInput();
        }

        // --- 2. Check for Teacher Conflicts (More Direct Query) ---
        // Checks if ANY of the selected teachers are already scheduled for this TimeSlot/Day
        $teacherConflict = TimetableEntry::where('day_of_week', $timeSlot->day_of_week)
            ->where('time_slot_id', $timeSlot->id)
            // Prevent self-conflict if this controller is reused for updates (though it's named CreateController)
            ->when($request->route('timetable_entry'), function ($query, $id) {
                return $query->where('id', '!=', $id);
            })
            ->whereHas('teachers', function ($query) use ($validated) {
                // Explicitly check for teacher ID in the pivot table (timetable_entry_teacher)
                $query->whereIn('teachers.id', $validated['teacher_ids']);
            })
            ->exists();

        if ($teacherConflict) {
            return back()->withErrors([
                'teacher_ids' => 'One or more selected teachers are already assigned for this period and day.',
            ])->withInput();
        }

        // --- 3. Create Timetable Entry ---
        $timetable = TimetableEntry::create([
            ...$validated, // Spread operator to include all validated fields
            'start_time'  => $timeSlot->start_time,
            'end_time'    => $timeSlot->end_time,
            'day_of_week' => $timeSlot->day_of_week,
        ]);

        // Attach teachers
        $timetable->teachers()->attach($validated['teacher_ids']);

        return to_route('timetable_entry:all')->with('toast', 'Timetable entry created successfully!');
    }
}
