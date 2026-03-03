<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageReadQueryServiceInterface;
use App\Packages\Domains\WorldHeritageReadQueryService;

class QueryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            WorldHeritageQueryServiceInterface::class,
            WorldHeritageQueryService::class
        );

        $this->app->bind(
            WorldHeritageReadQueryServiceInterface::class,
            WorldHeritageReadQueryService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
