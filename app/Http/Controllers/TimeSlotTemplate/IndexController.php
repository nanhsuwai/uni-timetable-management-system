<?php

namespace App\Http\Controllers\TimeSlotTemplate;

use App\Http\Controllers\Controller;
use App\Models\TimeSlotTemplate;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $timeSlotTemplates = TimeSlotTemplate::ordered()->get();

        if ($request->expectsJson()) {
            return response()->json($timeSlotTemplates);
        }

        return view('time-slot-templates.index', compact('timeSlotTemplates'));
    }
}
