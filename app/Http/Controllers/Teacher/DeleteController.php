<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;

class DeleteController extends Controller
{
    public function __invoke(Teacher $teacher)
    {
        $teacher->delete();

        return to_route('teacher:all')->with('toast', 'Teacher deleted successfully!');
    }
}
