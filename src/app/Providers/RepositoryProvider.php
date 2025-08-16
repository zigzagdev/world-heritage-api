<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Domains\WorldHeritageRepository;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            WorldHeritageRepositoryInterface::class,
            WorldHeritageRepository::class
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
