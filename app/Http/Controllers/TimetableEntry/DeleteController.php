<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\TimetableEntry;

class DeleteController extends Controller
{
    public function __invoke(TimetableEntry $timetableEntry)
    {
        $timetableEntry->delete();

        return to_route('timetable_entry:all')->with('toast', 'Timetable entry deleted successfully!');
    }
}
