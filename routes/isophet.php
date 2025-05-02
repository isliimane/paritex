<?php

use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath','isInstalled']
    ], function () {
        Route::get('get/geolocale',[\App\Http\Controllers\Admin\Addons\IshopetController::class,'geoLocale']);
});
