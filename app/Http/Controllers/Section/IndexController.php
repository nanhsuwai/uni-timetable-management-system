<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\Section;
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
        $academicLevels = AcademicLevel::orderBy('name')->get();
        return Inertia::render('Section/Index', [
            'sections' => $sections,
            'academicLevels' => $academicLevels,
            'filters' => $request->only('filterName', 'filterLevel'),
        ]);
    }
}
