<?php

namespace App\Http\Controllers\TimeSlot;

use App\Enums\SemesterName;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\TimeSlot;
use App\Models\TimeSlotTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = TimeSlot::with(['academicYear']);

        if ($request->has('filterName') && $request->filterName != '') {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        if ($request->has('filterDay') && $request->filterDay != '') {
            $query->where('day_of_week', $request->filterDay);
        }

        if ($request->has('filterAcademicYear') && $request->filterAcademicYear != '') {
            $query->where('academic_year_id', $request->filterAcademicYear);
        }

        if ($request->has('filterSemester') && $request->filterSemester != '') {
            $query->where('semester_id', $request->filterSemester);
        }

        $timeSlots = $query->with('timeSlotTemplate')->orderBy('day_of_week')->orderBy('start_time')->paginate(10)->withQueryString();
        $academicYears = AcademicYear::select('id', 'name')->get();
        $semesters = SemesterName::cases();
        $timeSlotTemplates = TimeSlotTemplate::active()->ordered()->get();

        return Inertia::render('TimeSlot/Index', [
            'timeSlots' => $timeSlots,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'timeSlotTemplates' => $timeSlotTemplates,
            'filters' => $request->only(['filterName', 'filterDay', 'filterAcademicYear', 'filterSemester']),
        ]);
    }
}
