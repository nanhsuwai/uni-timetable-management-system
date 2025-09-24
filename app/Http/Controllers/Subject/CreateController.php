<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:subjects,code',
            'name' => 'required|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        Subject::create([
            'code' => $request->code,
            'name' => $request->name,
            'academic_year_id' => $request->academic_year_id,
            'semester_id' => $request->semester_id,
        ]);

        return to_route('subject:all')->with('toast', 'Subject created successfully!');
    }
}
