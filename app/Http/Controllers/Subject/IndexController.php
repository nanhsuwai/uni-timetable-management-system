<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Subject::with(['academicYear', 'semester']);

        if ($request->has('filterCode') && $request->filterCode != '') {
            $query->where('code', 'like', '%' . $request->filterCode . '%');
        }

        if ($request->has('filterName') && $request->filterName != '') {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        $subjects = $query->orderBy('name')->paginate(10)->withQueryString();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        return Inertia::render('Subject/Index', [
            'subjects' => $subjects,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'filters' => $request->only('filterCode', 'filterName'),
        ]);
    }
}
