<?php

namespace App\Http\Controllers\AcademicProgram;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = AcademicProgram::with('academicYear');

        // Set default academic year filter to active year if not provided
        $filters = $request->only('filterName', 'filterAcademicYear');
        if (empty($filters['filterAcademicYear'])) {
            $activeAcademicYear = AcademicYear::getActive();
            $filters['filterAcademicYear'] = $activeAcademicYear ? $activeAcademicYear->id : null;
        }

        if ($request->has('filterName') && $request->filterName != '') {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        if ($request->has('filterAcademicYear') && $request->filterAcademicYear != '') {
            $query->where('academic_year_id', $request->filterAcademicYear);
        }

        $programs = $query->orderBy('name')->paginate(10)->withQueryString();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        // Get the default academic year for frontend
        $defaultAcademicYear = $filters['filterAcademicYear'] ? AcademicYear::find($filters['filterAcademicYear']) : $activeAcademicYear;

        $programOptions = ['CST', 'Computer Technology', 'Computer Science'];

        return Inertia::render('AcademicProgram/Index', [
            'programs' => $programs,
            'academicYears' => $academicYears,
            'programOptions' => $programOptions,
            'filters' => $filters,
            'defaultAcademicYear' => $defaultAcademicYear,
        ]);
    }
}
