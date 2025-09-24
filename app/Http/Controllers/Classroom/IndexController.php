<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Section;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Classroom::with('section');

        if ($request->has('filterRoomNo') && $request->filterRoomNo != '') {
            $query->where('room_no', 'like', '%' . $request->filterRoomNo . '%');
        }

        if ($request->has('filterSection') && $request->filterSection != '') {
            $query->where('section_id', $request->filterSection);
        }

        $classrooms = $query->orderBy('room_no')->paginate(10)->withQueryString();

        $sections = Section::orderBy('name')->get();
        return Inertia::render('Classroom/Index', [
            'classrooms' => $classrooms,
            'sections' => $sections,
            'filters' => $request->only('filterRoomNo', 'filterSection'),
        ]);
    }
}
