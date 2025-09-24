<?php

namespace App\Http\Controllers\AcademicProgram;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;

class DeleteController extends Controller
{
    public function __invoke(AcademicProgram $academicProgram)
    {
        $academicProgram->delete();

        return to_route('academic_program:all')->with('toast', 'Academic Program deleted successfully!');
    }
}
