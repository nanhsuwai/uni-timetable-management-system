<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class DeleteController extends Controller
{
    /**
     * Delete a specific teacher record.
     */
    public function __invoke(Teacher $teacher): RedirectResponse
    {
        try {
            // Attempt to delete the teacher record
            $teacher->delete();

            // Success redirect
            return to_route('teacher:all')->with('toast', '✅ Teacher deleted successfully!');

        } catch (QueryException $e) {
            // Check for specific constraint violation error code (e.g., MySQL 23000)
            if ($e->getCode() == '23000') {
                // Failure redirect with a specific error message for foreign key constraints
                return back()->with('toast', '❌ Cannot delete teacher. This teacher is currently assigned to subjects or classes and must be unassigned first.');
            }

            // Handle other database errors (less common)
            // Log the detailed error for debugging purposes
            logger()->error('Error deleting teacher ' . $teacher->id . ': ' . $e->getMessage());

            return back()->with('toast', '❌ An unexpected database error occurred during deletion.');
        } catch (\Exception $e) {
             // Handle non-database related exceptions (e.g., authorization failure, though authorization should be handled elsewhere)
            return back()->with('toast', '❌ An error occurred: ' . $e->getMessage());
        }
    }
}
