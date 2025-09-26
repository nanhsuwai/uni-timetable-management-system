<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\TimetableEntry;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\AcademicProgram;
use App\Models\AcademicLevel;
use App\Models\Section;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = TimetableEntry::with([
            'academicYear:id,name',
            'semester:id,name',
            'academicProgram:id,name',
            'academicLevel:id,name',
            'section:id,name',
            'classroom:id,room_no',
            'subject:id,name',
            'teachers:id,name',
            'timeSlot:id,name,start_time,end_time,academic_year_id',
        ]);

        $filters = [
            'filterYear' => 'academic_year_id',
            'filterSemester' => 'semester_id',
            'filterProgram' => 'program_id',
            'filterLevel' => 'level_id',
            'filterSection' => 'section_id',
            'filterClassroom' => 'classroom_id',
            'filterDay' => 'day_of_week',
        ];

        foreach ($filters as $requestKey => $column) {
            if ($request->filled($requestKey)) {
                $query->where($column, $request->$requestKey);
            }
        }

        $entries = $query->orderBy('day_of_week')->orderBy('start_time')->paginate(10);

        $referenceData = [
            'academicYears' => AcademicYear::select('id','name')->get(),
            'semesters' => Semester::select('id','name')->get(),
            'programs' => AcademicProgram::select('id','name','academic_year_id')->get(),
            'levels' => AcademicLevel::select('id','name','program_id')->get(),
            'sections' => Section::select('id','name','level_id')->get(),
            'classrooms' => Classroom::select('id','room_no','section_id')->get(),
            'subjects' => Subject::with('teachers:id,name')->select('id','name')->get(),
            'teachers' => Teacher::with('subjects:id,name')->select('id','name')->get(),
            'timeSlots' => TimeSlot::get(),
        ];

        return Inertia::render('TimetableEntry/Index', array_merge([
            'entries' => $entries,
            'filters' => $request->only(array_keys($filters)),
        ], $referenceData));
    }
}
