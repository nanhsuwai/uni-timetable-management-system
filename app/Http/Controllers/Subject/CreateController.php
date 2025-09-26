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
        $request->validate([
            'code' => 'required|string|unique:subjects,code',
            'name' => 'required|string',
            'status' => 'required|in:active,inactive',
            'level' => 'required|in:First Year,Second Year,Third Year,Fourth Year,Fifth Year,Master',
            'program' => 'required|in:Computer Foundation,Computer Technology,Computer Science,Master',
            'semester' => 'required|in:First Semester,Second Semester',
        ]);

        Subject::create([
            'code' => $request->code,
            'name' => $request->name,
            'status' => $request->status,
            'level' => $request->level,
            'program' => $request->program,
            'semester' => $request->semester,
        ]);

        return to_route('subject:all')->with('toast', 'Subject created successfully!');
    }
}
