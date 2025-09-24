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
        ]);

        Teacher::create([
            'code' => $request->code,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return to_route('teacher:all')->with('toast', 'Teacher created successfully!');
    }
}
