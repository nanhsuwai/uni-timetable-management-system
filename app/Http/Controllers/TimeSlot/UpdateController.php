<?php

namespace App\Http\Controllers\TimeSlot;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, TimeSlot $timeSlot)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $timeSlot->update([
            'name' => $request->name,
            'academic_year_id' => $request->academic_year_id,
            'semester_id' => $request->semester_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return to_route('time-slot:all')->with('toast', 'Time Slot updated successfully!');
    }
}
