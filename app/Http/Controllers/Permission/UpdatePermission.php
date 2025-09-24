<?php

namespace App\Http\Controllers\Permission;

use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UpdatePermission extends Controller
{
    public function __invoke(Permission $permission,Request $request){
        $permission->update([
            'name' => $request->permission_name,
            'description' => $request->description,
            'granted_system_id' => $request->granted_system,
            'module_name' =>$request->moduleName,//Department,Ministry,------
            'code' => Str::slug($request->permission_name, "_")//Slug name
    ]);
    }
}
