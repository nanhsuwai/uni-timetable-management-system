<?php

namespace App\Http\Controllers\AcademicLevel;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = AcademicLevel::with('academicProgram');

        if ($request->has('filterName') && $request->filterName != '') {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        if ($request->has('filterProgram') && $request->filterProgram != '') {
            $query->where('program_id', $request->filterProgram);
        }

        $levels = $query->orderBy('name')->paginate(10)->withQueryString();
        $academicPrograms = AcademicProgram::orderBy('name')->get();
        return Inertia::render('AcademicLevel/Index', [
            'levels' => $levels,
            'academicPrograms' => $academicPrograms,
            'filters' => $request->only('filterName', 'filterProgram'),
        ]);
    }
}
