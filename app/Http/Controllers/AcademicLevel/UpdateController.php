<?php

namespace App\Http\Controllers\AcademicLevel;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, AcademicLevel $academicLevel)
    {
        $request->validate([
            'program_id' => 'required|exists:academic_programs,id',
            'name' => 'required|string',
        ]);

        $academicLevel->update([
            'program_id' => $request->program_id,
            'name' => $request->name,
        ]);

        return to_route('academic_level:all')->with('toast', 'Academic Level updated successfully!');
    }
}
