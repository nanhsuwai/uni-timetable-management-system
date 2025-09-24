<?php

namespace App\Http\Controllers\User;

use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PermissionType;
use App\Models\UserHasPermission;

class EditController extends Controller
{
    public function __invoke(User $user, Request $request)
    {
        $type = Type::where('code', $request->user_type)->first();

        $user->update([
            'user_type' => $request->user_type,
        ]);

        $type_permissions = PermissionType::where("type_id", $type->id)->pluck('permission_id');

        $permissions = $type_permissions->toArray();

        $user->permissions()->sync($permissions);
    }
}
