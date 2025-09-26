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
            'code' => 'required|string|unique:teachers,code',
            'name' => 'required|string',
            'email' => 'nullable|email|unique:teachers,email',
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'head_of_department' => 'boolean',
        ]);

        Teacher::create([
            'code' => $request->code,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'department' => $request->department,
            'head_of_department' => $request->head_of_department ?? false,
        ]);

        return to_route('teacher:all')->with('toast', 'Teacher created successfully!');
    }
}
