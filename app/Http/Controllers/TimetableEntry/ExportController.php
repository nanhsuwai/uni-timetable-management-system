<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\TimetableExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'filterYear',
            'filterSemester',
            'filterProgram',
            'filterLevel',
            'filterSection'
        ]);

        return Excel::download(new TimetableExport($filters), 'timetable.xlsx');
    }
}
