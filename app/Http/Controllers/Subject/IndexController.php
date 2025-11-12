<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\LevelName;
use App\Enums\ProgramOption;
use App\Enums\SemesterName;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Subject::query();

        // Filters
        if ($request->filled('filterCode')) {
            $query->where('code', 'like', '%' . $request->filterCode . '%');
        }

        if ($request->filled('filterName')) {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        if ($request->filled('filterSemester')) {
            $query->where('semester', $request->filterSemester);
        }

        if ($request->filled('filterLevel')) {
            $query->where('level', $request->filterLevel);
        }

       /*  if ($request->filled('filterProgram')) {
            $query->where('program', $request->filterProgram);
        } */

        $subjects = $query->orderBy('name')->paginate(10)->withQueryString();

        // Enum dropdowns for frontend
        $semesters = SemesterName::cases();
        $levels = LevelName::cases();
        $programs = ProgramOption::cases();
        return Inertia::render('Subject/Index', [
            'subjects' => $subjects,
            'semesters' => $semesters,
            'levels' => $levels,
            'programs' => $programs,
            'filters' => $request->only('filterCode', 'filterName', 'filterSemester', 'filterLevel', 'filterProgram'),
        ]);
    }
}
