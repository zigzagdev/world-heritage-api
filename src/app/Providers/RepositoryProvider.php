<?php

namespace App\Providers;

use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\UserRepository;
use App\Packages\Domains\WorldHeritageRepository;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            WorldHeritageRepositoryInterface::class,
            WorldHeritageRepository::class
        );

        $this->app->bind(
            UserRepositroyInterface::class,
            UserRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
