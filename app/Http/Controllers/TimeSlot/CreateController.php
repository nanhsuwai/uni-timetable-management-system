<?php

namespace App\Http\Controllers\TimeSlot;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Check for duplicate time slot
        $exists = TimeSlot::where('academic_year_id', $request->academic_year_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                      });
            })
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'time_slot' => 'This time slot overlaps with an existing slot for the same academic year and day.'
            ]);
        }

        TimeSlot::create([
            'name' => $request->name,
            'academic_year_id' => $request->academic_year_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return to_route('time-slot:all')->with('toast', 'Time Slot created successfully!');
    }
}
