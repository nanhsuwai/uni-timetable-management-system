<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function __invoke(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return to_route('academic-year:all')->with('toast', 'Academic Year deleted successfully!');
    }
}
