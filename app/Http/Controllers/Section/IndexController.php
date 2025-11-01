<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Section::with('academicLevel');

        if ($request->has('filterName') && $request->filterName != '') {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        if ($request->has('filterLevel') && $request->filterLevel != '') {
            $query->where('level_id', $request->filterLevel);
        }

        $sections = $query->orderBy('name')->paginate(10)->withQueryString();
        $academicLevels = AcademicLevel::where('status', 'active')->orderBy('name')->get();
        $teachers = Teacher::select('id', 'name')
    ->where('status', 'approved')
    ->orderBy('name')
    ->get();

       /*  $teachers = Teacher::where('status', 'active')->orderBy('name')->get(); */
        return Inertia::render('Section/Index', [
            'sections' => $sections,
            'academicLevels' => $academicLevels,
            'teachers' => $teachers,
            'filters' => $request->only('filterName', 'filterLevel'),
        ]);
    }
}
