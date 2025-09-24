<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\TimetableEntry;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, TimetableEntry $timetableEntry)
    {
        // dd($request->all());
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
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
        ]);
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

        $timetableEntry->update($data);

        // Sync teachers to the timetable entry
        if ($request->has('teacher_ids')) {
            $timetableEntry->teachers()->sync($request->teacher_ids);
        }

        return to_route('timetable_entry:all')->with('toast', 'Timetable entry updated successfully!');
    }
}
