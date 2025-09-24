<?php

namespace App\Providers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register() : void
    {
        //
    }

    public function boot() : void
    {
        if(env('APP_ENV')=='local'){
            URL::forceScheme('http');
        }else{
            URL::forceScheme('https');
        }
        // $this->app['request']->server->set('HTTPS','on');
        $this->loadMigrationsFrom(config('migration.paths'));


        if (!Collection::hasMacro('paginateCollection')) {

            Collection::macro('paginateCollection',
                function ($perPage = 15, $page = null, $options = []) {
                $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
                return (new LengthAwarePaginator(
                        collect($this)->forPage($page, $perPage)->values()->all(),
                        count($this),
                        $perPage,
                        $page,
                        $options
                    )
                )->withPath('');
            });
        }

    }
}
