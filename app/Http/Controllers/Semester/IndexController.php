<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->only(['filterName', 'filterProgram']);

        $semesters = Semester::with('academicProgram')
            ->when($filters['filterName'] ?? null, fn($query, $name) =>
                $query->where('name', 'like', "%{$name}%")
            )
            ->when($filters['filterProgram'] ?? null, fn($query, $programId) =>
                $query->where('program_id', $programId)
            )
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $academicPrograms = AcademicProgram::orderBy('name')->get();

        return Inertia::render('Semester/Index', [
            'semesters'        => $semesters,
            'academicPrograms' => $academicPrograms,
            'filters'          => $filters,
        ]);
    }
}
