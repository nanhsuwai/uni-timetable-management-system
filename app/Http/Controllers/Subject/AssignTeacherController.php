<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssignTeacherController extends Controller
{
    /**
     * Show the teacher assignment interface for a subject.
     */
    public function show(Subject $subject)
    {
        $subject->load([
            'teachers:id,name,code'
        ]);

        $availableTeachers = Teacher::whereDoesntHave('subjects', function($query) use ($subject) {
            $query->where('subject_id', $subject->id);
        })->select('id', 'name', 'code')->get();

        $assignedTeachers = $subject->teachers;

        return Inertia::render('Subject/AssignTeacher', [
            'subject' => $subject,
            'availableTeachers' => $availableTeachers,
            'assignedTeachers' => $assignedTeachers,
        ]);
    }

    /**
     * Assign teachers to a subject.
     */
    public function assign(Request $request, Subject $subject)
    {
        $request->validate([
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'exists:teachers,id'
        ]);

        try {
            DB::beginTransaction();

            // Remove existing assignments
            $subject->teachers()->detach();

            // Assign new teachers
            if (!empty($request->teacher_ids)) {
                $subject->teachers()->attach($request->teacher_ids);
            }

            DB::commit();

            return redirect()->route('subject:assign-teacher', $subject)
                ->with('success', 'Teachers assigned successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to assign teachers. Please try again.');
        }
    }

    /**
     * Remove a teacher from a subject.
     */
    public function removeTeacher(Subject $subject, Teacher $teacher)
    {
        try {
            $subject->teachers()->detach($teacher->id);

            return redirect()->back()
                ->with('success', 'Teacher removed successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to remove teacher. Please try again.');
        }
    }

    /**
     * Get teachers assigned to a subject for API.
     */
    public function getAssignedTeachers(Subject $subject)
    {
        $teachers = $subject->teachers()->select('id', 'name', 'code')->get();

        return response()->json([
            'teachers' => $teachers
        ]);
    }
}
