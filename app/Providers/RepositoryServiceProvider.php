<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\Admin\ClaimInterface;
use App\Repositories\ClaimRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
           \App\Repositories\Interfaces\Admin\ClaimInterface::class,
            \App\Repositories\ClaimRepository::class
        );    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
