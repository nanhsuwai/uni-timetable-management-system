<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;

class DeleteController extends Controller
{
    public function __invoke(Section $section)
    {
        $section->delete();

        return to_route('section:all')->with('toast', 'Section deleted successfully!');
    }
}
