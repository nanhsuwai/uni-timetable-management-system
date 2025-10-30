<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Permission;
use App\Notifications\TeacherApprovedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StatusController extends Controller
{
    public function __invoke(Request $request, Teacher $teacher)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        if ($request->status === 'approved') {

            // Update teacher status to active
            $teacher->update(['status' => 'approved']);

            // Create user account if not exists
            if (!$teacher->user_id) {
                // Generate a secure random password
                $password = Str::random(8);

                $user = User::create([
                    'name' => $teacher->name,
                    'username' => $this->generateUsername($teacher->name),
                    'email' => $teacher->email,
                    'password' => Hash::make($password),
                    'user_type' => 'teacher',
                ]);

                // Link teacher to user
                $teacher->update(['user_id' => $user->id]);

                // Assign teacher permissions
                // $teacherPermissions = Permission::where('name', 'like', '%teacher%')->get();
                // $user->permissions()->attach($teacherPermissions);

                // // Send email notification with credentials
                $user->notify(new TeacherApprovedNotification($teacher, $password));
            }

            return redirect()->back()->with('toast', '✅ Teacher approved and account created successfully. Email sent with login credentials.');
        } elseif ($request->status === 'rejected') {
            $teacher->update(['status' => 'rejected']);

            return redirect()->back()->with('toast', '❌ Teacher application rejected.');
        }
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
