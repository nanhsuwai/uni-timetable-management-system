<?php

namespace App\Http\Controllers\Subject;

use App\Enums\LevelName;
use App\Enums\ProgramOption;
use App\Enums\SemesterName;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        // 1. Update Validation to expect an array of programs
        $request->validate([
            'code' => 'nullable|string|unique:subjects,code',
            'name' => 'required|string',
            'status' => 'required|in:active,inactive',
            'level' => 'required|in:First Year,Second Year,Third Year,Fourth Year,Fifth Year',
            
            // New validation for multiple programs (using 'programs' field name)
            // It must be an array, and each element must be a string and one of the allowed options.
            'programs' => ['required', 'array', 'min:1'],
            'programs.*' => ['required', 'string', 'in:CST,Computer Technology,Computer Science,Diploma'],
            
            'semester' => 'required|in:First Semester,Second Semester',
        ]);

        // 2. Convert the array of programs to a comma-separated string
        // This is necessary because your database structure appears to use a single column ('program')
        $programsString = implode(', ', $request->programs);

        // 3. Create the Subject using the converted string
        Subject::create([
            'code' => $request->code,
            'name' => $request->name,
            'status' => $request->status,
            'level' => $request->level,
            // Use the new variable for the 'program' column
            'program' => $programsString,
            'semester' => $request->semester,
        ]);

        return to_route('subject:all')->with('toast', '✅ Subject created successfully!');
    }
}