<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Section $section)
    {
        $request->validate([
            'level_id' => 'required|exists:academic_levels,id',
            'name' => 'required|string',
        ]);

        $section->update([
            'level_id' => $request->level_id,
            'name' => $request->name,
        ]);

        return to_route('section:all')->with('toast', 'Section updated successfully!');
    }
}
