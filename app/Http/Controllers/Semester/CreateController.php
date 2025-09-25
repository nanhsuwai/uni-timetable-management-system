<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(Semester::getValidationRules());

        Semester::create([
            'academic_year_id' => $request->academic_year_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return to_route('semester:all')->with('toast', 'Semester created successfully!');
    }
}
