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
            'code' => 'required|string|unique:teachers,code,' . $teacher->id,
            'name' => 'required|string',
            'email' => 'nullable|email|unique:teachers,email,' . $teacher->id,
            'phone' => 'nullable|string',
        ]);

        $teacher->update([
            'code' => $request->code,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return to_route('teacher:all')->with('toast', 'Teacher updated successfully!');
    }
}
