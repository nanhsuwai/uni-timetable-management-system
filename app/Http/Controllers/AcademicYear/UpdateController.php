<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|string|unique:academic_years,name,' . $academicYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive,archived',
        ]);

        $data = [
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
        ];

        $academicYear->update($data);

        return to_route('academic-year:all')->with('toast', 'Academic Year updated successfully!');
    }
}
