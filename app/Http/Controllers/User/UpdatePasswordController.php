<?php

namespace App\Http\Controllers\User;

use Throwable;
use App\Models\User;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class UpdatePasswordController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        try {
            $request->validate([
                'password' => 'required|confirmed'
            ]);

                $user->update([
                    'password' => bcrypt($request->password)
                ]);
            
            return to_route('users:all')->with('toast', 'Password updated!');
                
            
        } catch (Throwable $e) {
            return to_route('users:all')->with('toast', 'Something went wrong'. $e->getMessage());
        }
    }
}
