<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\Semester;
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
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'exists:teachers,id',
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
            return back()->withErrors(['error' => 'The selected academic year must be active.'])->withInput();
        }

        // Check for time slot conflicts
        $conflicts = $this->checkTimeSlotConflicts($request);
        if ($conflicts) {
            return back()->withErrors(['error' => 'There is a conflict with an existing timetable entry for the same section or classroom at the specified time.'])->withInput();
        }

        // Check for subject frequency constraints
        $frequencyConflict = $this->checkSubjectFrequency($request);
        if ($frequencyConflict) {
            return back()->withErrors(['error' => 'The subject can only be scheduled up to 2 times per day and 4 times per week for this section.'])->withInput();
        }

        // Check for teacher period frequency constraints
        $teacherFrequencyConflict = $this->checkTeacherFrequency($request);
        if ($teacherFrequencyConflict) {
            return back()->withErrors(['error' => 'One or more teachers exceed the allowed number of periods per day (max 2).'])->withInput();
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
        $duplicatePeriod = TimetableEntry::where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('section_id', $data['section_id'])
            ->where('subject_id', $data['subject_id'])
            ->where('time_slot_id', $data['time_slot_id'])
            ->first();

        if ($duplicatePeriod) {
            return back()->withErrors(['error' => 'This subject is already scheduled for the selected section in the same time slot.'])->withInput();
        }
        $checkDuplicateTeacher = TimetableEntry::where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('time_slot_id', $data['time_slot_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where(function ($q) use ($data) {
                $q->where('section_id', $data['section_id'])
                  ->orWhere('classroom_id', $data['classroom_id']);
            })
            ->whereHas('teachers', function ($q) use ($request) {
                $q->whereIn('teacher_id', $request->teacher_ids);
            })
            ->first();
        if ($checkDuplicateTeacher) {
            return back()->withErrors(['error' => 'One or more selected teachers are already assigned to another class during the selected time slot.'])->withInput();
        }
     //timetable entry count  for each section of a program in a semester should not exceed 40
        $timetableEntryCount = TimetableEntry::where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('section_id', $data['section_id'])
            ->count();
        $semester = Semester::where('academic_year_id',$data['academic_year_id'])->where('id',$data['semester_id'])->first();
        $timeSlotCount = TimeSlot::where('academic_year_id',$data['academic_year_id'])->where('semester',$semester->name)->count();
        if ($timetableEntryCount >= $timeSlotCount) {
            return back()->withErrors(['error' => 'The number of timetable entries for this section exceeds the available time slots.'])->withInput();
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

    private function checkTeacherFrequency(Request $request)
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

        // For each teacher, check max 2 periods per day
        foreach ($request->teacher_ids as $teacherId) {
            $dailyCount = \App\Models\TimetableEntry::whereHas('teachers', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })
            ->where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('day_of_week', $dayOfWeek)
            ->count();

            if ($dailyCount >= 2) {
                return true;
            }
        }

        return false;
    }
}
