<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Models\Semester;

class DeleteController extends Controller
{
    public function __invoke(Semester $semester)
    {
        $semester->delete();

        return to_route('semester:all')->with('toast', 'Semester deleted successfully!');
    }
}
