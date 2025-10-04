<?php

namespace App\Providers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register() : void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot() : void
    {
        // Force URL scheme depending on environment
        if (env('APP_ENV') === 'local') {
            URL::forceScheme('http');
        } else {
            URL::forceScheme('https');
        }

        // Load migrations from config path (if defined)
        if (config('migration.paths')) {
            $this->loadMigrationsFrom(config('migration.paths'));
        }

        // Custom pagination macro for collections
        if (! Collection::hasMacro('paginateCollection')) {
            Collection::macro('paginateCollection', function (
                int $perPage = 15,
                ?int $page = null,
                array $options = []
            ) {
                /** @var \Illuminate\Support\Collection $this */
                $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                return new LengthAwarePaginator(
                    $this->forPage($page, $perPage)->values(),
                    $this->count(),
                    $perPage,
                    $page,
                    $options
                );
            });
        }
    }
}
