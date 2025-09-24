<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Subject $subject)
    {
        $request->validate([
            'code' => 'required|string|unique:subjects,code,' . $subject->id,
            'name' => 'required|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $subject->update([
            'code' => $request->code,
            'name' => $request->name,
            'academic_year_id' => $request->academic_year_id,
            'semester_id' => $request->semester_id,
        ]);

        return to_route('subject:all')->with('toast', 'Subject updated successfully!');
    }
}
