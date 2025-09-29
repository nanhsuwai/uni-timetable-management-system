<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\TimetableEntry;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'program_id' => 'required|exists:academic_programs,id',
            'level_id' => 'required|exists:academic_levels,id',
            'section_id' => 'required|exists:sections,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            // 'teacher_ids' => 'required|array|min:1',
            // 'teacher_ids.*' => 'exists:teachers,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'day_of_week' => 'nullable|string',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
        ]);

        // Ensure the academic year is active
        $academicYear = \App\Models\AcademicYear::find($request->academic_year_id);
        if (!$academicYear || !$academicYear->isActive()) {
            return back()->withErrors(['academic_year_id' => 'The selected academic year must be active.'])->withInput();
        }

        // Check for time slot conflicts
        $conflicts = $this->checkTimeSlotConflicts($request);
        if ($conflicts) {
            return back()->withErrors(['time_slot_id' => 'There is a conflict with an existing timetable entry for the same section or classroom at the specified time.'])->withInput();
        }

        // Check for subject frequency constraints
        $frequencyConflict = $this->checkSubjectFrequency($request);
        if ($frequencyConflict) {
            return back()->withErrors(['subject_id' => 'The subject can only be scheduled up to 2 times per day and 4 times per week for this section.'])->withInput();
        }

// dd($request->all());
        $data = $request->except('teacher_ids');

        // If time_slot_id is provided, populate start_time, end_time, and day_of_week from the time slot
        if ($request->filled('time_slot_id')) {
            $timeSlot = TimeSlot::find($request->time_slot_id);
            if ($timeSlot) {
                $data['start_time'] = $timeSlot->start_time;
                $data['end_time'] = $timeSlot->end_time;
                $data['day_of_week'] = $timeSlot->day_of_week;
            }
        }

        $timetableEntry = TimetableEntry::create($data);

        // Attach teachers to the timetable entry
        if ($request->has('teacher_ids')) {
            $timetableEntry->teachers()->attach($request->teacher_ids);
        }

        return to_route('timetable_entry:all')->with('toast', 'Timetable entry created successfully!');
    }

    private function checkTimeSlotConflicts(Request $request)
    {
        $query = TimetableEntry::where('academic_year_id', $request->academic_year_id)
                                ->where('semester_id', $request->semester_id)
                                ->where('day_of_week', $request->day_of_week);

        // If time_slot_id is provided, get the time details
        if ($request->filled('time_slot_id')) {
            $timeSlot = TimeSlot::find($request->time_slot_id);
            if ($timeSlot) {
                $startTime = $timeSlot->start_time;
                $endTime = $timeSlot->end_time;
            }
        } else {
            $startTime = $request->start_time;
            $endTime = $request->end_time;
        }

        if ($startTime && $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($q2) use ($startTime, $endTime) {
                    $q2->where('start_time', '<', $endTime)
                       ->where('end_time', '>', $startTime);
                });
            });
        }

        // Check for conflicts with the same section or classroom
        $conflicts = $query->where(function ($q) use ($request) {
            $q->where('section_id', $request->section_id)
              ->orWhere('classroom_id', $request->classroom_id);
        })->exists();

        return $conflicts;
    }

    private function checkSubjectFrequency(Request $request)
    {
        // Derive day_of_week if time_slot_id is provided
        $dayOfWeek = $request->day_of_week;
        if ($request->filled('time_slot_id')) {
            $timeSlot = TimeSlot::find($request->time_slot_id);
            if ($timeSlot) {
                $dayOfWeek = $timeSlot->day_of_week;
            }
        }

        if (!$dayOfWeek) {
            return true; // Conflict if day cannot be determined
        }

        // Check daily frequency: max 2 times per day
        $dailyCount = TimetableEntry::where('academic_year_id', $request->academic_year_id)
                                    ->where('semester_id', $request->semester_id)
                                    ->where('section_id', $request->section_id)
                                    ->where('subject_id', $request->subject_id)
                                    ->where('day_of_week', $dayOfWeek)
                                    ->count();

        if ($dailyCount >= 2) {
            return true;
        }

        // Check weekly frequency: max 4 times per week
        $weeklyCount = TimetableEntry::where('academic_year_id', $request->academic_year_id)
                                     ->where('semester_id', $request->semester_id)
                                     ->where('section_id', $request->section_id)
                                     ->where('subject_id', $request->subject_id)
                                     ->count();

        if ($weeklyCount >= 4) {
            return true;
        }

        return false;
    }
}
