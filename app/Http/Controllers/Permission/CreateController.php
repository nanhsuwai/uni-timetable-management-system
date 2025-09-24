<?php

namespace App\Http\Controllers\Permission;

use App\Models\Type;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PermissionType;
use App\Http\Controllers\Controller;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $type = Type::findOrFail($request->type_id);

        if(!$type) {
            foreach($request->checkedPermission as $permission) {
                $permission_type = PermissionType::updateOrCreate([
                    'type_id' => $type->id,
                    'permission_id' => $permission
                ]);
            }
        } else {
            $type->permissions()->sync($request->checkedPermission);
        }

        $users = User::where('user_type', $type->code)->get(); // getting all users by user_type

        // update each user to request checked permissions
        foreach($users as $user) {
            $user->permissions()->sync($request->checkedPermission);
            $user->refresh();
        }

        return to_route('permissions:all')->with('toast', 'Permission created...');
    }
}
