<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Channels\FcmChannel;

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
        Notification::extend('fcm', function ($app) {
            return new FcmChannel();
        });
    }
}