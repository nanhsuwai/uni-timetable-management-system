<?php

namespace App\Http\Controllers\AcademicProgram;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'names' => 'required|array|min:1',
            'names.*' => 'in:Computer Foundation,Computer Technology,Computer Science,Master',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $createdCount = 0;
            foreach ($request->names as $name) {
                AcademicProgram::create([
                    'academic_year_id' => $request->academic_year_id,
                    'name' => $name,
                    'status' => $request->status,
                ]);
                $createdCount++;
            }

            $message = $createdCount === 1
                ? 'Academic Program created successfully!'
                : "{$createdCount} Academic Programs created successfully!";

            return to_route('academic_program:all')->with('toast', $message);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                return back()->withErrors([
                    'names' => 'One or more selected program names already exist for the chosen academic year. Please choose different program names or select a different academic year.'
                ])->withInput();
            }

            // Re-throw other database exceptions
            throw $e;
        }
    }
}
