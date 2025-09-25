<?php

namespace App\Http\Controllers\Semester;

use App\Models\AcademicYear;
use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->only(['filterName', 'filterProgram', 'filterAcademicYear']);

        // Set default academic year if not provided
        if (empty($filters['filterAcademicYear'])) {
            $activeAcademicYear = AcademicYear::getActive();
            $filters['filterAcademicYear'] = $activeAcademicYear ? $activeAcademicYear->id : null;
        }

        $semesters = Semester::with(['academicProgram', 'academicYear'])
            ->when($filters['filterName'] ?? null, fn($query, $name) =>
                $query->where('name', 'like', "%{$name}%")
            )
            ->when($filters['filterProgram'] ?? null, fn($query, $programId) =>
                $query->where('program_id', $programId)
            )
            ->when($filters['filterAcademicYear'] ?? null, fn($query, $yearId) =>
                $query->where('academic_year_id', $yearId)
            )
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $academicYears = AcademicYear::orderBy('name')->get();
        $academicPrograms = AcademicProgram::orderBy('name')->get();

        // Get the default academic year for frontend
        $defaultAcademicYear = $filters['filterAcademicYear'] ? AcademicYear::find($filters['filterAcademicYear']) : $activeAcademicYear;

        return Inertia::render('Semester/Index', [
            'semesters'        => $semesters,
            'academicYears'    => $academicYears,
            'academicPrograms' => $academicPrograms,
            'filters'          => $filters,
            'defaultAcademicYear' => $defaultAcademicYear,
        ]);
    }
}
