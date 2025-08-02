<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Packages\Domains\WorldHeritageQueryService;

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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
