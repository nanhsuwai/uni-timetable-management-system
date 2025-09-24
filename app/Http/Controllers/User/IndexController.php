<?php

namespace App\Http\Controllers\User;

use App\Models\Type;
use App\Models\User;
use Inertia\Inertia;

use Illuminate\Http\Request;
use App\Models\UserHasPermission;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;

class IndexController extends Controller
{
    public function __invoke(Request $request) {

        $types = Type::select('id', 'name', 'code')->get();

        $data = User::with('permissions:id,name,granted_system_id')
                ->when($request->filterUsername, function($q, $filterUsername) {
                    $q->where('name', 'like', '%'.$filterUsername.'%');
                })
                ->when($request->filterType, function($q, $filterType) {
                    $q->where('user_type', $filterType['code']);
                })
                ->paginate(10)->withQueryString();
        $users = UserResource::collection($data);

        return Inertia::render('User/Index',[
            'users' => $users,
            'types' => $types,
            'userTypes' => Type::select('code', 'name')->get(),
            'filters' => $request->only('filterUsername', 'filterType'),
        ]);
    }
}



