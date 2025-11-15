<?php

namespace App\Http\Controllers\AcademicLevel;

use App\Enums\LevelName;
use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\SemesterName;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        // Filter academic programs to only those in active academic year
        $activeAcademicYear = AcademicYear::getActiveYears()->first();
        $academicPrograms = AcademicProgram::where('academic_year_id', $activeAcademicYear?->id)
            ->orderBy('name')
            ->get();

        // Build the query for academic levels
        $query = AcademicLevel::with([
            'academicProgram',
            'sections',
            'sections.classroom',
            'sections.sectionHeadTeacher'
        ])->whereIn('program_id', $academicPrograms->pluck('id'));

        // Apply filters
        if ($request->filled('filterName')) {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        if ($request->filled('filterProgram')) {
            $query->where('program_id', $request->filterProgram);
        }

        if ($request->filled('filterSemester')) {
            $query->where('semester', $request->filterSemester);
        }

        // Paginate results
        $levels = $query->orderBy('name')->paginate(10)->withQueryString();

        // Other data for view
        $classrooms = Classroom::orderBy('room_no')->get();
        $assignClassrooms = Classroom::where('is_available', false)->orderBy('room_no')->get();
        $teachers = Teacher::where('status', 'approved')->orderBy('name')->get();
        $assignTeachers = Teacher::whereIn('id', function ($query) {
            $query->select('section_head_teacher_id')
                  ->from('sections')
                  ->whereNotNull('section_head_teacher_id');
        })->orderBy('name')->get();

        return Inertia::render('AcademicLevel/Index', [
            'levels' => $levels,
            'assignClassrooms' => $assignClassrooms,
            'assignTeachers' => $assignTeachers,
            'academicYears' => AcademicYear::select('id','name')->get(),
            'academicPrograms' => $academicPrograms,
            'classrooms' => $classrooms,
            'teachers' => $teachers,
            'filters' => $request->only('filterName', 'filterProgram', 'filterSemester'),
            'fixedLevels' => LevelName::cases(),
            'semesters' => SemesterName::cases(), // or use SemesterName::cases() if you have an Enum
        ]);
    }
}
