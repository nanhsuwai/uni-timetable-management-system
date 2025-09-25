<?php

namespace App\Http\Controllers\AcademicProgram;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class UpdateController extends Controller
{
    public function __invoke(Request $request, AcademicProgram $academicProgram)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'names' => 'required|array|min:1|max:1',
            // 'names.*' => 'in:Computer Foundation,Computer Technology,Computer Science,Master',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $academicProgram->update([
                'academic_year_id' => $request->academic_year_id,
                'name' => $request->names[0],
                'status' => $request->status,
            ]);

            return to_route('academic_program:all')->with('toast', 'Academic Program updated successfully!');
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                return back()->withErrors([
                    'names' => 'The selected program name already exists for the chosen academic year. Please choose a different program name or select a different academic year.'
                ])->withInput();
            }

            // Re-throw other database exceptions
            throw $e;
        }
    }
}
