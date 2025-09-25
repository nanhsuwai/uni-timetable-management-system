<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Semester $semester)
    {
        $request->validate(Semester::getValidationRules());

        $semester->update([
            'academic_year_id' => $request->academic_year_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return to_route('semester:all')->with('toast', 'Semester updated successfully!');
    }
}
