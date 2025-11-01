<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Teacher::query();

        if ($request->has('filterCode') && $request->filterCode != '') {
            $query->where('code', 'like', '%' . $request->filterCode . '%');
        }

        if ($request->has('filterName') && $request->filterName != '') {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        if ($request->has('filterEmail') && $request->filterEmail != '') {
            $query->where('email', 'like', '%' . $request->filterEmail . '%');
        }

        if ($request->has('filterDepartment') && $request->filterDepartment != '') {
            $query->where('department', 'like', '%' . $request->filterDepartment . '%');
        }

        if ($request->has('filterStatus') && $request->filterStatus != '') {
            $query->where('status', $request->filterStatus);
        }

        $teachers = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Teacher/Index', [
            'teachers' => $teachers,
            'filters' => $request->only('filterCode', 'filterName', 'filterEmail', 'filterDepartment', 'filterStatus'),
        ]);
    }

    /**
     * Approve a teacher application
     */
    public function approve(Request $request, Teacher $teacher)
    {
        // Update teacher status to active
        $teacher->update(['status' => 'active']);

        // Create user account
        $user = User::create([
            'name' => $teacher->name,
            'username' => $this->generateUsername($teacher->name),
            'email' => $teacher->email,
            'password' => Hash::make('password123'), // Default password, should be changed on first login
            'user_type' => 'teacher',
        ]);

        // Link teacher to user
        $teacher->update(['user_id' => $user->id]);

        // Assign teacher permissions
        $teacherPermissions = Permission::where('name', 'like', '%teacher%')->get();
        $user->permissions()->attach($teacherPermissions);

        return redirect()->back()->with('success', 'Teacher application approved and account created successfully.');
    }

    /**
     * Reject a teacher application
     */
    public function reject(Request $request, Teacher $teacher)
    {
        $teacher->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Teacher application rejected.');
    }

    /**
     * Generate a unique username from teacher name
     */
    private function generateUsername($name)
    {
        $baseUsername = strtolower(str_replace(' ', '', $name));
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
