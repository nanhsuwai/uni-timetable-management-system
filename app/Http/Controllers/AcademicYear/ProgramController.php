<?php

namespace App\Http\Controllers\AcademicYear;

use App\Enums\ProgramOption;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function index(AcademicYear $academicYear)
    {
        $programs = $academicYear->academicPrograms()->orderBy('name')->paginate(10);
        $academic_year = AcademicYear::find($academicYear->id);
        $programOptions = ProgramOption::cases();

        return Inertia::render('AcademicYear/Programs', [
            'academicYear' => $academic_year,
            'programOptions' => $programOptions,

            'programs' => $programs,
        ]);
    }
    public function create(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'names' => 'required|array|min:1',
            // 'names.*' => 'in:Computer Foundation,Computer Technology,Computer Science,Master',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $createdCount = 0;

            foreach ($request->names as $name) {
                $academicYear->academicPrograms()->create([
                    'name'   => $name,
                    'status' => $request->status,
                ]);
                $createdCount++;
            }

            $message = $createdCount === 1
                ? 'Academic Program created successfully!'
                : "{$createdCount} Academic Programs created successfully!";

            return to_route('academic-year:programs', $academicYear->id)
                ->with('toast', $message);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate entry)
                return back()->withErrors([
                    'names' => 'One or more selected program names already exist for this academic year. Please choose different program names.'
                ])->withInput();
            }

            throw $e; // rethrow unexpected DB errors
        }
    }
}
