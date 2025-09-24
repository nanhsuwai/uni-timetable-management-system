<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PermissionType;
use App\Models\Type;
use App\Models\UserHasPermission;

class CreatePermissionController extends Controller
{
    public function __invoke(User $user,Request $request)
    {
        $checkedPermissions = $request->checkedPermission;

        if($checkedPermissions) {
            $user->permissions()->sync($checkedPermissions);
        }
    }
}
