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

class GridViewController extends Controller
{
    public function __invoke(Request $request)
    {
        // dd($request->all());
        $query = TimetableEntry::with([
            'academicYear:id,name',
            'semester:id,name',
            'academicProgram:id,name',
            'academicLevel:id,name',
            'section:id,name',
            'classroom:id,room_no',
            'subject:id,name,code',
            'teachers:id,name',
            'timeSlot:id,name,start_time,end_time,day_of_week,academic_year_id,is_lunch_period',
        ]);

        $filters = [
            'filterYear' => 'academic_year_id',
            'filterSemester' => 'semester_id',
            'filterProgram' => 'program_id',
            'filterLevel' => 'level_id',
            'filterSection' => 'section_id',
            'filterClassroom' => 'classroom_id',
        ];

        foreach ($filters as $requestKey => $column) {
            if ($request->filled($requestKey)) {
                $query->where($column, $request->$requestKey);
            }
        }

        // Also filter time slots by academic year if specified
        if ($request->filled('filterYear')) {
            $query->whereHas('timeSlot', function($q) use ($request) {
                $q->where('academic_year_id', $request->filterYear);
            });
        }

        // Get all entries without pagination for grid view
        $entries = $query->orderBy('day_of_week')->orderBy('start_time')->get();
// dd($entries->first());
        $referenceData = [
            'academicYears' => AcademicYear::select('id','name')->get(),
            'semesters' => Semester::select('id','name','academic_year_id')->get(),
            'programs' => AcademicProgram::select('id','name','academic_year_id')->get(),
            'levels' => AcademicLevel::select('id','name','program_id')->get(),
            'sections' => Section::select('id','name','level_id')->get(),
            'subjects' => Subject::select('id','name','code')->get(),
            'teachers' => Teacher::select('id','name')->get(),
            'classrooms' => Classroom::select('id','room_no','section_id')->get(),
            'timeSlots' => TimeSlot::select('id','name','start_time','end_time','day_of_week','academic_year_id','is_lunch_period')->get(),
        ];

        return Inertia::render('TimetableEntry/GridView', array_merge([
            'entries' => $entries,
            'filters' => $request->only(array_keys($filters)),
        ], $referenceData));
    }
}
