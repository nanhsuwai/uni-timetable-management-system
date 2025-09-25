<?php

namespace App\Http\Controllers;

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
use App\Helpers\helpers;

class WelcomeController extends Controller
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
            'timeSlot:id,name,start_time,end_time,day_of_week,academic_year_id,is_lunch_period',
        ]);

        // Define filter mappings
        $filters = [
            'filterYear' => 'academic_year_id',
            'filterSemester' => 'semester_id',
            'filterProgram' => 'program_id',
            'filterLevel' => 'level_id',
            'filterSection' => 'section_id',
        ];

        // Apply filters if provided
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

        // Get current active academic year and semester for default display
        $currentAcademicYear = _getActiveAcademicYear();
        $currentSemester = Semester::where('status', 'active')->first();

        // If no filters are applied, show current academic year and semester data
        if (!$request->hasAny(array_keys($filters))) {
            if ($currentAcademicYear) {
                $query->where('academic_year_id', $currentAcademicYear->id);
            }
            if ($currentSemester) {
                $query->where('semester_id', $currentSemester->id);
            }
        }

        // Always filter by active academic year if no specific year filter is provided
        if (!$request->filled('filterYear') && $currentAcademicYear) {
            $query->where('academic_year_id', $currentAcademicYear->id);
        }

        // Get all entries without pagination for grid view
        $entries = $query->orderBy('day_of_week')->orderBy('start_time')->get();

        // Get active academic year for filtering reference data
        $activeAcademicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;

        $referenceData = [
            'academicYears' => AcademicYear::select('id','name')->get(),
            'semesters' => Semester::select('id','name','academic_year_id')->get(),
            'programs' => AcademicProgram::select('id','name','academic_year_id')->get(),
            'levels' => AcademicLevel::select('id','name','program_id')->get(),
            'sections' => Section::select('id','name','level_id')->get(),
            'subjects' => Subject::select('id','name', 'academic_year_id', 'semester_id')->get(),
            'teachers' => Teacher::select('id','name')->get(),
            'classrooms' => Classroom::select('id','room_no','section_id')->get(),
            'timeSlots' => TimeSlot::select('id','name','start_time','end_time','day_of_week','academic_year_id','is_lunch_period')->get(),
        ];

        // If we have an active academic year, filter reference data accordingly
        if ($activeAcademicYearId) {
            $referenceData['programs'] = $referenceData['programs']->where('academic_year_id', $activeAcademicYearId);
            $referenceData['subjects'] = $referenceData['subjects']->where('academic_year_id', $activeAcademicYearId);
            $referenceData['timeSlots'] = $referenceData['timeSlots']->where('academic_year_id', $activeAcademicYearId);

            // Filter levels and sections based on programs that belong to active academic year
            $activeProgramIds = $referenceData['programs']->pluck('id')->toArray();
            if (!empty($activeProgramIds)) {
                $referenceData['levels'] = $referenceData['levels']->whereIn('program_id', $activeProgramIds);

                // Filter sections based on levels that belong to active programs
                $activeLevelIds = $referenceData['levels']->pluck('id')->toArray();
                if (!empty($activeLevelIds)) {
                    $referenceData['sections'] = $referenceData['sections']->whereIn('level_id', $activeLevelIds);
                }
            }
            // Filter semesters by active academic year
            $referenceData['semesters'] = $referenceData['semesters']->where('academic_year_id', $activeAcademicYearId);
        }

        return Inertia::render('Welcome', array_merge([
            'entries' => $entries,
            'filters' => $request->only(array_keys($filters)),
        ], $referenceData));
    }
}
