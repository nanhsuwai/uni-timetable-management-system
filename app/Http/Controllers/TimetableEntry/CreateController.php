<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\TimetableEntry;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            // ✅ Validate request input
            $validated = $request->validate([
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

            // ✅ Ensure academic year is active
            $academicYear = \App\Models\AcademicYear::find($validated['academic_year_id']);
            if (!$academicYear || !$academicYear->isActive()) {
                return back()->withErrors(['error' => 'The selected academic year must be active.'])->withInput();
            }

            // ✅ Check time slot conflicts
            if ($this->checkTimeSlotConflicts($request)) {
                return back()->withErrors(['error' => 'Conflict: another timetable entry exists for the same section or classroom at this time.'])->withInput();
            }

            // ✅ Check subject frequency constraints
            if ($this->checkSubjectFrequency($request)) {
                return back()->withErrors(['error' => 'Subject can only be scheduled up to 2 times per day and 4 times per week.'])->withInput();
            }

            // ✅ Check teacher frequency constraints
            if ($this->checkTeacherFrequency($request)) {
                return back()->withErrors(['error' => 'One or more teachers exceed the daily limit (max 2 periods).'])->withInput();
            }

            // ✅ Prepare data
            $data = $request->except('teacher_ids');

            // ✅ Populate time slot data
            if ($request->filled('time_slot_id')) {
                $timeSlot = TimeSlot::find($request->time_slot_id);
                if ($timeSlot) {
                    $data['start_time'] = $timeSlot->start_time;
                    $data['end_time'] = $timeSlot->end_time;
                    $data['day_of_week'] = $timeSlot->day_of_week;
                }
            }

            // ✅ Prevent duplicate subject entry
            $duplicatePeriod = TimetableEntry::where('academic_year_id', $data['academic_year_id'])
                ->where('semester_id', $data['semester_id'])
                ->where('section_id', $data['section_id'])
                ->where('subject_id', $data['subject_id'])
                ->where('time_slot_id', $data['time_slot_id'])
                ->first();

            if ($duplicatePeriod) {
                return back()->withErrors(['error' => 'This subject is already scheduled for this section and time slot.'])->withInput();
            }

            // ✅ Prevent duplicate teacher schedule conflict
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
                return back()->withErrors(['error' => 'One or more teachers already have a class in this time slot.'])->withInput();
            }

            // ✅ Ensure timetable entry count does not exceed available time slots
            $timetableEntryCount = TimetableEntry::where('academic_year_id', $data['academic_year_id'])
                ->where('semester_id', $data['semester_id'])
                ->where('section_id', $data['section_id'])
                ->count();

            $semester = Semester::where('academic_year_id', $data['academic_year_id'])
                ->where('id', $data['semester_id'])
                ->first();

            $timeSlotCount = TimeSlot::where('academic_year_id', $data['academic_year_id'])
                ->where('semester', $semester->name)
                ->count();

            if ($timetableEntryCount >= $timeSlotCount) {
                return back()->withErrors(['error' => 'The timetable entries for this section exceed available time slots.'])->withInput();
            }

            // ✅ Create timetable entry
            $timetableEntry = TimetableEntry::create($data);

            // ✅ Attach teachers
            if ($request->has('teacher_ids')) {
                $timetableEntry->teachers()->syncWithoutDetaching($request->teacher_ids);
            }

            return to_route('timetable_entry:all')->with('toast', 'Timetable entry created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors specifically
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            // Catch any other unexpected errors
            Log::error('Timetable creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'An unexpected error occurred. Please try again later.'])->withInput();
        }
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
