<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\{
    AcademicYear,
    Semester,
    TimetableEntry,
    TimeSlot
};
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        /** ✅ Validate input */
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id'      => 'required|exists:semesters,id',
            'program_id'       => 'required|exists:academic_programs,id',
            'level_id'         => 'required|exists:academic_levels,id',
            'section_id'       => 'required|exists:sections,id',
            'classroom_id'     => 'required|exists:classrooms,id',
            'subject_id'       => 'required|exists:subjects,id',
            'teacher_ids'      => 'required|array|min:1',
            'teacher_ids.*'    => 'exists:teachers,id',
            'time_slot_id'     => 'required|exists:time_slots,id',
        ]);

        /** ✅ Ensure academic year is active */
        $academicYear = AcademicYear::find($validated['academic_year_id']);
        if (!$academicYear || !$academicYear->isActive()) {
            return back()->withErrors(['error' => 'The selected academic year must be active.'])->withInput();
        }

        /** ✅ Retrieve time slot details */
        $timeSlot = TimeSlot::find($validated['time_slot_id']);
        if (!$timeSlot) {
            return back()->withErrors(['error' => 'Invalid time slot.'])->withInput();
        }

        $validated = array_merge($validated, [
            'start_time'  => $timeSlot->start_time,
            'end_time'    => $timeSlot->end_time,
            'day_of_week' => $timeSlot->day_of_week,
        ]);

        /** ✅ Check for duplicate subject in same time slot */
        $duplicate = TimetableEntry::where([
            'academic_year_id' => $validated['academic_year_id'],
            'semester_id'      => $validated['semester_id'],
            'section_id'       => $validated['section_id'],
            'subject_id'       => $validated['subject_id'],
            'time_slot_id'     => $validated['time_slot_id'],
        ])->exists();

        if ($duplicate) {
            return back()->withErrors([
                'error' => 'This subject already exists in this section and time slot.'
            ])->withInput();
        }

        /** ✅ Prevent teacher double-booking (same time, same day) */
        $teacherConflict = TimetableEntry::where('academic_year_id', $validated['academic_year_id'])
            ->where('semester_id', $validated['semester_id'])
            ->where('time_slot_id', $validated['time_slot_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->whereHas('teachers', fn($q) => $q->whereIn('teacher_id', $validated['teacher_ids']))
            ->exists();

        if ($teacherConflict) {
            return back()->withErrors([
                'error' => 'One or more selected teachers are already assigned at this time slot on ' . ucfirst($validated['day_of_week']) . '.'
            ])->withInput();
        }

        /** ✅ Check subject frequency rules (2/day, 4/week) */
        if ($msg = $this->subjectFrequencyExceeded($validated)) {
            return back()->withErrors(['error' => $msg])->withInput();
        }

    

        /** ✅ Ensure total slots not exceeded */
        $totalEntries = TimetableEntry::where([
            'academic_year_id' => $validated['academic_year_id'],
            'semester_id'      => $validated['semester_id'],
            'section_id'       => $validated['section_id'],
        ])->count();

        $semester = Semester::find($validated['semester_id']);
        $totalSlots = TimeSlot::where('academic_year_id', $validated['academic_year_id'])
            ->where('semester', $semester->name)
            ->count();

        if ($totalEntries >= $totalSlots) {
            return back()->withErrors([
                'error' => 'Timetable entries for this section exceed available time slots.'
            ])->withInput();
        }

        /** ✅ Create timetable entry */
        $timetable = TimetableEntry::create($validated);
        $timetable->teachers()->attach($validated['teacher_ids']);

        return to_route('timetable_entry:all')->with('toast', 'Timetable entry created successfully!');
    }

    /*** ✅ Subject Frequency Rule (2/day, 4/week per subject only) ***/
    private function subjectFrequencyExceeded(array $data): ?string
    {
        $query = TimetableEntry::where([
            'academic_year_id' => $data['academic_year_id'],
            'semester_id'      => $data['semester_id'],
            'section_id'       => $data['section_id'],
            'subject_id'       => $data['subject_id'],
        ]);

        $dailyCount  = (clone $query)->where('day_of_week', $data['day_of_week'])->count();
        $weeklyCount = $query->count();

        if ($dailyCount >= 2) {
            return '❌ This subject already appears 2 times today for this section.';
        }

        if ($weeklyCount >= 4) {
            return '❌ This subject already appears 4 times this week for this section.';
        }

        return null;
    }

    /*** ✅ Teacher Daily Limit Rule (2/day) ***/
    private function teacherDailyLimitExceeded(array $data): bool
    {
        $entries = TimetableEntry::where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->with('teachers')
            ->get();

        foreach ($entries as $entry) {
            foreach ($entry->teachers as $teacher) {
                if (in_array($teacher->id, $data['teacher_ids'])) {
                    // Count all teacher’s entries on the same day
                    $teacherDailyCount = TimetableEntry::whereHas('teachers', fn($q) => $q->where('teacher_id', $teacher->id))
                        ->where('day_of_week', $data['day_of_week'])
                        ->count();

                    if ($teacherDailyCount >= 2) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
