<?php

namespace App\Http\Controllers\User\Permission;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionTypes\PermissionTypeResource;
use App\Models\Permission;
use App\Models\PermissionType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke()
    {
        $permission_types = PermissionTypeResource(PermissionType::get());

        return Inertia::render('User/Permission/Index',[
            'permission_types' => $permission_types
        ]);
    }
}
