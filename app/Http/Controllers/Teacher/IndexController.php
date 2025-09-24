<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Teacher::query();

        if ($request->has('filterCode') && $request->filterCode != '') {
            $query->where('code', 'like', '%' . $request->filterCode . '%');
        }

        if ($request->has('filterName') && $request->filterName != '') {
            $query->where('name', 'like', '%' . $request->filterName . '%');
        }

        if ($request->has('filterEmail') && $request->filterEmail != '') {
            $query->where('email', 'like', '%' . $request->filterEmail . '%');
        }

        $teachers = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Teacher/Index', [
            'teachers' => $teachers,
            'filters' => $request->only('filterCode', 'filterName', 'filterEmail'),
        ]);
    }
}
