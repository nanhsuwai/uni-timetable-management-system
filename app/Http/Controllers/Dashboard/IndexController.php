<?php

namespace App\Http\Controllers\Dashboard;
use Inertia\Inertia;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\AcademicProgram;
use App\Models\AcademicLevel;
use App\Models\Section;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Helpers\helpers;


class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        // Get current active academic year and semester
        $currentAcademicYears = AcademicYear::getActiveYears();
        /* $currentAcademicYear = _getActiveAcademicYear(); */
        $currentSemester = Semester::where('status', 'active')->first();

        // Get statistics data
        $stats = [
            'total_academic_years' => AcademicYear::count(),
            'total_semesters' => Semester::count(),
            'total_programs' => AcademicProgram::count(),
            'total_levels' => AcademicLevel::count(),
            'total_sections' => Section::count(),
            'total_classrooms' => Classroom::count(),
            'total_subjects' => Subject::count(),
            'total_teachers' => Teacher::count(),
            'total_timetable_entries' => TimetableEntry::count(),
            'total_users' => User::count(),
            'active_academic_years' => $currentAcademicYears->pluck('name')->implode(', '),
            'active_semester' => $currentSemester ? $currentSemester->name : 'None',
        ];

        // Get recent activities (last 10 timetable entries)
        $recentActivities = TimetableEntry::with([
            'academicYear',
            'semester',
            'academicProgram',
            'academicLevel',
            'section',
            'classroom',
            'subject',
            'teachers'
        ])
        ->latest()
        ->limit(10)
        ->get()
        ->map(function ($entry) {
            return [
                'id' => $entry->id,
                'subject' => $entry->subject->name ?? 'N/A',
                'classroom' => $entry->classroom->name ?? 'N/A',
                'section' => $entry->section->name ?? 'N/A',
                'day_of_week' => $entry->day_of_week,
                'start_time' => $entry->start_time,
                'end_time' => $entry->end_time,
                'teachers' => $entry->teachers->pluck('name')->join(', '),
                'created_at' => $entry->created_at->format('M d, Y H:i'),
            ];
        });

        // Get subject distribution for chart
        $subjectDistribution = Subject::select('name', DB::raw('count(*) as count'))
            ->groupBy('name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($subject) {
                return [
                    'name' => $subject->name,
                    'count' => $subject->count
                ];
            });

        // Get teacher workload for chart
        $teacherWorkload = Teacher::withCount('subjects')
            ->orderBy('subjects_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($teacher) {
                return [
                    'name' => $teacher->name,
                    'subjects_count' => $teacher->subjects_count
                ];
            });

        // Get timetable entries by day
        $entriesByDay = TimetableEntry::select('day_of_week', DB::raw('count(*) as count'))
            ->groupBy('day_of_week')
            ->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get()
            ->map(function ($entry) {
                return [
                    'day' => ucfirst($entry->day_of_week),
                    'count' => $entry->count
                ];
            });

        // Get timetable entries by time slot
        $entriesByTimeSlot = TimetableEntry::select('time_slot_id', DB::raw('count(*) as count'))
            ->with('timeSlot')
            ->groupBy('time_slot_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($entry) {
                return [
                    'time_slot' => $entry->timeSlot->name ?? 'N/A',
                    'count' => $entry->count
                ];
            });

        // Get filter options
        $academicYears = AcademicYear::select('id', 'name')->orderBy('name')->get();
        $semesters = Semester::select('id', 'name')->orderBy('name')->get();
        $academicPrograms = AcademicProgram::select('id', 'name')->orderBy('name')->get();
        $academicLevels = AcademicLevel::select('id', 'name')->orderBy('name')->get();
        $sections = Section::select('id', 'name')->orderBy('name')->get();

        // Get section timetables
        $sectionTimetables = Section::with([
            'timetableEntries' => function ($query) {
                $query->with(['subject', 'teachers', 'classroom', 'timeSlot'])
                      ->orderBy('day_of_week')
                      ->orderBy('start_time');
            }
        ])->get()->map(function ($section) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'timetable' => $section->timetableEntries->map(function ($entry) {
                    return [
                        'day' => $entry->day_of_week,
                        'start_time' => $entry->start_time,
                        'end_time' => $entry->end_time,
                        'subject' => $entry->subject->name ?? 'N/A',
                        'teachers' => $entry->teachers->pluck('name')->join(', '),
                        'classroom' => $entry->classroom->name ?? 'N/A',
                    ];
                })
            ];
        });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'subjectDistribution' => $subjectDistribution,
            'teacherWorkload' => $teacherWorkload,
            'entriesByDay' => $entriesByDay,
            'entriesByTimeSlot' => $entriesByTimeSlot,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'academicPrograms' => $academicPrograms,
            'academicLevels' => $academicLevels,
            'sections' => $sections,
            'sectionTimetables' => $sectionTimetables,
        ]);
    }
}
