<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Teacher $teacher)
    {
        $request->validate([
           /*  'code' => 'required|string|unique:teachers,code,' . $teacher->id, */
            'name' => 'required|string',
            'email' => 'nullable|email|unique:teachers,email,' . $teacher->id,
            'phone' => 'nullable|string',
            'department' => 'required|string',
            'head_of_department' => 'boolean',
        ]);

        $teacher->update([
            /* 'code' => $request->code, */
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'department' => $request->department,
            'head_of_department' => $request->head_of_department ?? false,
        ]);

        return to_route('teacher:all')->with('toast', 'Teacher updated successfully!');
    }
}
