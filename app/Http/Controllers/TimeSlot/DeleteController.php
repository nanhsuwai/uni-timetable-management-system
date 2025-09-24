<?php

namespace App\Http\Controllers\TimeSlot;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function __invoke(TimeSlot $timeSlot)
    {
        $timeSlot->delete();

        return to_route('time-slot:all')->with('toast', 'Time Slot deleted successfully!');
    }
}
