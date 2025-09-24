<?php

namespace App\Http\Controllers\Permission;

use App\Models\Type;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionTypes\PermissionTypeResource;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
       
        $permission_types = PermissionTypeResource::collection(Type::paginate(10)->withQueryString());

        return Inertia::render('Permission/Index',[
            'permission_types' => $permission_types,
        ]);
    }
}
