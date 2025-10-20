<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\TimetableEntry;
use App\Exports\TimetableExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportExcel(Request $request)
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
            'timeSlot:id,name,start_time,end_time',
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

        $query->orderBy('day_of_week')->orderBy('start_time');

        return Excel::download(new TimetableExport($request), 'timetable_entries.xlsx');
    }
}
