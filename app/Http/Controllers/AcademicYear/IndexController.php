<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = AcademicYear::query();

        if ($request->has('filterName') && $request->filterName != '') {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        $academicYears = $query->orderBy('start_date', 'desc')->paginate(10)->withQueryString();
        return Inertia::render('AcademicYear/Index', [
            'academicYears' => $academicYears,
            'filters' => $request->only('filterName'),
        ]);
    }
}
