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

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = AcademicLevel::with('academicProgram', 'sections', 'sections.classroom', 'sections.sectionHeadTeacher');

        if ($request->has('filterName') && $request->filterName != '') {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        if ($request->has('filterProgram') && $request->filterProgram != '') {
            $query->where('program_id', $request->filterProgram);
        }

        // Filter academic programs to only those in active academic year
        $activeAcademicYear = AcademicYear::getActive();
        $academicPrograms =  AcademicProgram::where('academic_year_id', $activeAcademicYear?->id)->orderBy('name')->get()
;

        $levels = $query->whereIn('program_id', $academicPrograms->pluck('id'))->orderBy('name')->paginate(10)->withQueryString();
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
            'filters' => $request->only('filterName', 'filterProgram'),
            'fixedLevels' => LevelName::cases(),
        ]);
    }
}
