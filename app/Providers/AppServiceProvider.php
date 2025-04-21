<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Schema::defaultStringLength(191);
        if (Schema::hasTable('languages')) {
            app_config();
        }

        //return 
        View::composer('admin.partials.sidebar', function ($view) {
            $view->with('pendingReturnsCount', ReturnRequest::where('status', 'pending')->count());
        });
    }
}