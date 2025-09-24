<?php

namespace App\Http\Controllers\AcademicLevel;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:academic_programs,id',
            'name' => 'required|string',
        ]);

        AcademicLevel::create([
            'program_id' => $request->program_id,
            'name' => $request->name,
        ]);

        return to_route('academic_level:all')->with('toast', 'Academic Level created successfully!');
    }
}
