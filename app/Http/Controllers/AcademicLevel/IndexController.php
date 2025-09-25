<?php

namespace App\Http\Controllers\AcademicLevel;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\AcademicYear;
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

        // Filter academic programs to only those in active academic year
        $activeAcademicYear = AcademicYear::getActive();
        $academicPrograms = $activeAcademicYear
            ? AcademicProgram::where('academic_year_id', $activeAcademicYear->id)->orderBy('name')->get()
            : AcademicProgram::orderBy('name')->get();

        $levels = $query->whereIn('program_id', $academicPrograms->pluck('id'))->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('AcademicLevel/Index', [
            'levels' => $levels,
            'academicPrograms' => $academicPrograms,
            'filters' => $request->only('filterName', 'filterProgram'),
        ]);
    }
}
