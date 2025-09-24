<?php

namespace App\Http\Middleware;

use App\Models\UserApiToken;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {   
        abort_if($request->user_id == null || $request->user_token == null, 403, 'Authorization Denied !');
        // $sampleToken = "kan27GxX4cMmg2gSBEmBGKtUpLC3mEWr7Rl2kxfJEGJAFXEjLD3pspBFRHgAXDuRvKfq95xlqhBOBuo6";
        $userApiToken = UserApiToken::where('token', $request->user_token)->first();
        if($request->user_id == $userApiToken->user_id && $request->user_token == $userApiToken->token) {
            // return $next($request);
            if(! auth()->check()) {
                Auth::loginUsingId($request->user_id);
            }
            return redirect()->route('requested:candidates:all');
        }
        return abort(403, 'Unauthorized...');
    }
}
