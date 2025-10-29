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
use Illuminate\Support\Facades\DB; // <-- 1. Import DB Facade

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
            'filterSubject' => 'subject_id',
            'filterDay' => 'day_of_week',
        ];

        foreach ($filters as $requestKey => $column) {
            if ($request->filled($requestKey)) {
                $query->where($column, $request->$requestKey);
            }
        }

        // 🎯 OPTIMIZATION: Custom Ordering by Day of the Week (Monday to Friday)
        $query->orderBy(DB::raw("FIELD(LOWER(day_of_week), 'monday', 'tuesday', 'wednesday', 'thursday', 'friday')"));

        // Keep the secondary order by time for chronological sorting within each day
        $query->orderBy('start_time');

        $entries = $query->paginate(10);
        $activeFilters = $request->only(array_keys($filters));
$entries = $query->paginate(10)->appends($activeFilters); 
        
        // OPTIMIZATION: Consolidate the reference data fetch using collection methods (optional)
        $referenceData = [
            'academicYears' => AcademicYear::select('id','name')->get(),
            'semesters' => Semester::select('id','name','academic_year_id')->get(),
            'programs' => AcademicProgram::select('id','name','academic_year_id')->get(),
            'levels' => AcademicLevel::select('id','name','program_id')->get(),
            'sections' => Section::select('id','name','level_id')->get(),
            'classrooms' => Classroom::select('id','room_no','section_id')->get(),
            'subjects' => Subject::with('teachers:id,name')->select('id','name','semester')->get(),
            'teachers' => Teacher::with('subjects:id,name')->select('id','name')->get(),
            'timeSlots' => TimeSlot::get(),
        ];

        return Inertia::render('TimetableEntry/Index', array_merge([
            'entries' => $entries,
            'filters' => $request->only(array_keys($filters)),
        ], $referenceData));
    }
}