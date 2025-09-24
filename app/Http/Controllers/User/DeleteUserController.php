<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\DB;

class DeleteUserController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
         DB::beginTransaction();

         try{
            $user->permissions()->sync([]);
            $user->delete();
            DB::commit();
         }catch(Exception $e) {
            return $e->getMessage();
         }
    }
}
