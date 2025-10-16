<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\{
    TimetableEntry,
    AcademicYear,
    AcademicProgram,
    AcademicLevel,
    Classroom,
    Section,
    Semester,
    Subject,
    Teacher,
    TimeSlot
};
use Illuminate\Http\Request;
use App\Templates\TimetableTemplate;

class GenerateTimetableController extends Controller
{
    public function __invoke(Request $request)
    {
        // 🧩 1. Build Base Query with Relations
        $query = TimetableEntry::with([
            'academicYear:id,name',
            'semester:id,name',
            'academicProgram:id,name',
            'academicLevel:id,name',
            'section:id,name,level_id,section_head_teacher_id',
            'section.sectionHeadTeacher:id,name',
            'classroom:id,room_no,section_id',
            'subject:id,name,code',
            'teachers:id,name',
            'timeSlot:id,name,start_time,end_time,day_of_week,academic_year_id,is_lunch_period',
        ]);

        // 🎯 2. Apply Filters (Year, Semester, Program, Level, Section, Classroom)
        $filters = [
            'filterYear'     => 'academic_year_id',
            'filterSemester' => 'semester_id',
            'filterProgram'  => 'program_id',
            'filterLevel'    => 'level_id',
            'filterSection'  => 'section_id',
            'filterClassroom' => 'classroom_id',
        ];

        foreach ($filters as $requestKey => $column) {
            if ($request->filled($requestKey)) {
                $query->where($column, $request->$requestKey);
            }
        }

        // 📅 3. Filter TimeSlots by Academic Year if provided
        if ($request->filled('filterYear')) {
            $query->whereHas(
                'timeSlot',
                fn($q) =>
                $q->where('academic_year_id', $request->filterYear)
            );
        }

        // 📋 4. Retrieve Entries
        $entries = $query
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        if ($entries->isEmpty()) {
            return response()->json(['message' => 'No timetable entries found.'], 404);
        }

        // 📘 5. Reference Data (for dropdowns, lookups, etc.)
        $referenceData = [
            'academicYears' => AcademicYear::select('id', 'name')->get(),
            'semesters'     => Semester::select('id', 'name', 'academic_year_id')->get(),
            'programs'      => AcademicProgram::select('id', 'name', 'academic_year_id')->get(),
            'levels'        => AcademicLevel::select('id', 'name', 'program_id')->get(),
            'sections'      => Section::with(['sectionHeadTeacher', 'classroom'])
                ->select('id', 'name', 'level_id', 'section_head_teacher_id')
                ->get(),
            'subjects'      => Subject::select('id', 'name', 'code')->get(),
            'teachers'      => Teacher::select('id', 'name')->get(),
            'classrooms'    => Classroom::select('id', 'room_no', 'section_id')->get(),
            'timeSlots'     => TimeSlot::select('id', 'name', 'start_time', 'end_time', 'day_of_week', 'academic_year_id', 'is_lunch_period')->get(),
        ];

        // 🧠 6. Resolve Active Filters or Fallback from First Entry
        $firstEntry = $entries->first();

        $academicYear = $referenceData['academicYears']->firstWhere('id', $request->filterYear ?? $firstEntry?->academic_year_id);
        $semester     = $referenceData['semesters']->firstWhere('id', $request->filterSemester ?? $firstEntry?->semester_id);
        $program      = $referenceData['programs']->firstWhere('id', $request->filterProgram ?? $firstEntry?->program_id);
        $level        = $referenceData['levels']->firstWhere('id', $request->filterLevel ?? $firstEntry?->level_id);
        $section      = $referenceData['sections']->firstWhere('id', $request->filterSection ?? $firstEntry?->section_id);

        // 🖨️ 7. Generate PDF with TimetableTemplate
        $template = new TimetableTemplate(
            entries: $entries,
            timeSlots: $referenceData['timeSlots'],
            academicYear: $academicYear,
            semester: $semester,
            program: $program,
            level: $level,
            section: $section
        );

        // 🪶 8. Return the PDF Response
        return response($template->generate())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="timetable.pdf"');
    }
}
