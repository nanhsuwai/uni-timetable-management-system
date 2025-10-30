<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class TeacherRegisteredController extends Controller
{
    /**
     * Display the teacher registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/TeacherRegister');
    }

    /**
     * Handle an incoming teacher registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // dd('Store method called');
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:teachers,email',
            'phone' => 'required|string|max:20',
            'department' => 'required|string|max:255',
        ]);

        // Create teacher with pending status
        Teacher::create([
            'code' => _generateTeacherCode(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'department' => $request->department,
            'status' => 'pending',
        ]);

        // Redirect back to welcome page with success message
        return redirect()->route('welcome')->with('toast', 'Your teacher application has been submitted successfully. You will be notified once it is reviewed by an administrator.');
    }
}
