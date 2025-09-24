<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use App\Models\Subject;

class DeleteController extends Controller
{
    public function __invoke(Subject $subject)
    {
        $subject->delete();

        return to_route('subject:all')->with('toast', 'Subject deleted successfully!');
    }
}
