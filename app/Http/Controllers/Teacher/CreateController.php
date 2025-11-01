<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([

            'name' => 'required|string',
            'email' => 'nullable|email|unique:teachers,email',
            'phone' => 'nullable|string',
            'department' => 'required|string',
            'head_of_department' => 'boolean',
        ]);

        Teacher::create([
            'code' => _generateTeacherCode(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'department' => $request->department,
            'head_of_department' => $request->head_of_department ?? false,
        ]);

        return to_route('teacher:all')->with('toast', 'Teacher created successfully!');
    }
}
