<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Check for overlapping dates with existing academic years
        $overlapping = \App\Models\AcademicYear::where(function ($query) use ($request) {
            $query->where('start_date', '<=', $request->end_date)
                  ->where('end_date', '>=', $request->start_date);
        })->exists();

        if ($overlapping) {
            return back()->withErrors(['start_date' => 'The selected dates overlap with an existing academic year.']);
        }

        AcademicYear::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'active',
        ]);

        return to_route('academic-year:all')->with('toast', 'Academic Year created successfully!');
    }
}
