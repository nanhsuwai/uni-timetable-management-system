<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Services\LMS\ApiService;
use App\Services\Password\GeneratePassword;
use Illuminate\Support\Facades\Hash;

class CreateController extends Controller
{
    public function __invoke(Request $request) {
       

        User::create([
            'username' => $request->username,
            'name' => $request->username,
            'email' => $request->email,
            'user_type' => $request->user_type,
            'password' => $request->password, // Hash::make($request->password),
        ]);

        return to_route('users:all')->with('toast', 'Success !');
    }
}