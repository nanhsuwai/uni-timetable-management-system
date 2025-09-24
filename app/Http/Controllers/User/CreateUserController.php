<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Services\Image\ImageService;
use App\Services\Password\GeneratePassword;
use Illuminate\Support\Facades\Hash;

class CreateUserController extends Controller
{
    public function __invoke(Request $request)
    {       
            $randomPassword = env('Password');
            if(env('APP_ENV') == 'production') {
                $randomPassword = GeneratePassword::generatePassword(10);
            }

            // create user account in lms first
            $request_name = $request->english_name;
            $explodedName = explode(" ",$request_name);
            $last_name = end($explodedName);
            $explodedFirstName = array_diff($explodedName, [$last_name]);
            $first_name = implode(" ", $explodedFirstName);



            $user = User::create([
                'name' => $request->english_name,
                'username' => Str::slug($request->username, "_"),
                'email' => $request->email,
                'email_verified_at' => Carbon::now(),
                'user_type' => 'admin',
                'password' => Hash::make($randomPassword), //random Password
            ]);

            return to_route('users:all')->with('toast', 'Success !');

       
    }


}
