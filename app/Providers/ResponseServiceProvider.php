<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot() : void
    {
        Response::macro('success', function ($message,$status = 200) {
            return response()
            ->json(['message' => $message],$status);
        });
        Response::macro('error', function ($message,$status = 500) {
            return response()
            ->json([
                    'status' => false,
                    'message' => $message ?? "Oops something wrong"
                ], $status
            );
        });

    }
}
