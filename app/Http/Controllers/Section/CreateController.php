<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'level_id' => 'required|exists:academic_levels,id',
            'name' => 'required|string',
        ]);

        Section::create([
            'level_id' => $request->level_id,
            'name' => $request->name,
        ]);

        return to_route('section:all')->with('toast', 'Section created successfully!');
    }
}
