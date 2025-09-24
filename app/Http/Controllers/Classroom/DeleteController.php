<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Models\Classroom;

class DeleteController extends Controller
{
    public function __invoke(Classroom $classroom)
    {
        $classroom->delete();

        return to_route('classroom:all')->with('toast', 'Classroom deleted successfully!');
    }
}
