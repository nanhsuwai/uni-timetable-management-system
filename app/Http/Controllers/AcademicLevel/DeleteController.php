<?php

namespace App\Http\Controllers\AcademicLevel;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;

class DeleteController extends Controller
{
    public function __invoke(AcademicLevel $academicLevel)
    {
        $academicLevel->delete();

        return to_route('academic_level:all')->with('toast', 'Academic Level deleted successfully!');
    }
}
