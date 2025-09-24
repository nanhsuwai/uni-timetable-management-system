<?php

namespace App\Http\Controllers\AcademicProgram;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string',
        ]);

        AcademicProgram::create([
            'academic_year_id' => $request->academic_year_id,
            'name' => $request->name,
        ]);

        return to_route('academic_program:all')->with('toast', 'Academic Program created successfully!');
    }
}
