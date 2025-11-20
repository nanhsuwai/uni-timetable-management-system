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
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB; // <-- 1. Import DB Facade

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $id = auth()->id();
        $teacher = User::find($id);
        $isTeacher = $teacher->isTeacher();
        $subjectIds = $teacher->teacher?->subjects->pluck('id')->toArray();
        /* dd( $teacherSubjects); */
        if ($isTeacher) {
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
            ])->whereHas('subject', function ($q) use ($subjectIds) {
                $q->whereIn('id', $subjectIds);
            });
        } else {
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
        }

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

        /*  foreach ($filters as $requestKey => $column) {
            if ($request->filled('filterSection')) {
                $section = Section::where('name', $request->filterSection)->first();
                if ($section) {

                    $query->where('section_id', $section->id);
                }
            }

            if ($request->filled($requestKey)) {
                $query->where($column, $request->$requestKey);
            }
        } */
        if ($request->filled('filterYear')) {
            // Get the AcademicYear record based on the name from request
            $year = AcademicYear::where('name', $request->filterYear)->first();

            // Make sure a year was found
            if ($year) {
                $query->where('academic_year_id', $year->id);
            }
        }
        if ($request->filled('filterSemester')) {
            // Get the AcademicYear record based on the name from request
            $semester = Semester::where('name', $request->filterSemester)->first();

            // Make sure a semester was found
            if ($semester) {
                $query->where('semester_id', $semester->id);
            }
        }
        if ($request->filled('filterProgram')) {
            // Get the AcademicYear record based on the name from request
            $program = AcademicProgram::where('name', $request->filterProgram)->first();

            // Make sure a year was found
            if ($program) {
                $query->where('program_id', $program->id);
            }
        }
        if ($request->filled('filterLevel')) {
            // Get all level IDs that match the name
            $levelIds = AcademicLevel::where('name', $request->filterLevel)->pluck('id')->toArray();

            // Only filter if we found matching IDs
            if (!empty($levelIds)) {
                $query->whereIn('level_id', $levelIds);
            }
        }
        if ($request->filled('filterSection')) {
            // Get the Section record based on the name from request
            $sectionIds = Section::where('name', $request->filterSection)->pluck('id')->toArray();
            // Make sure a section was found
            if (!empty($sectionIds)) {
                $query->whereIn('section_id', $sectionIds);
            }
        }
        if ($request->filled('filterDay')) {
            $filterDay =  strtolower($request->filterDay);
            if ($filterDay) {
                $query->where('day_of_week', $filterDay);
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
        if ($isTeacher) {
            $referenceData = [
                'academicYears' => AcademicYear::select('id', 'name')->get(),
                'semesters' => Semester::select('id', 'name', 'academic_year_id')->get(),
                'programs' => AcademicProgram::select('id', 'name', 'academic_year_id')->get(),
                'levels' => AcademicLevel::select('id', 'name', 'program_id', 'semester_id')->get(),
                'sections' => Section::select('id', 'name', 'level_id')->distinct('name')->get(),
                'classrooms' => Classroom::select('id', 'room_no', 'section_id')->get(),
                'subjects' => Subject::with('teachers:id,name')->select('id', 'name', 'semester')->whereIn('id', $subjectIds)->get(),
                'teachers' => Teacher::with('subjects:id,name')->select('id', 'name')->get(),
                'timeSlots' => TimeSlot::get(),
            ];
        } else {
            $referenceData = [
                'academicYears' => AcademicYear::select('id', 'name')->get(),
                'semesters' => Semester::select('id', 'name', 'academic_year_id')->get(),
                'programs' => AcademicProgram::select('id', 'name', 'academic_year_id')->get(),
                'levels' => AcademicLevel::select('id', 'name', 'program_id', 'semester_id')->get(),
                'sections' => Section::select('id', 'name', 'level_id')->distinct('name')->get(),
                'classrooms' => Classroom::select('id', 'room_no', 'section_id')->get(),
                'subjects' => Subject::with('teachers:id,name')->select('id', 'name', 'semester')->get(),
                'teachers' => Teacher::with('subjects:id,name')->select('id', 'name')->get(),
                'timeSlots' => TimeSlot::get(),
            ];
        }
        return Inertia::render('TimetableEntry/Index', array_merge([
            'entries' => $entries,
            'filters' => $request->only(array_keys($filters)),
        ], $referenceData));
    }
}
