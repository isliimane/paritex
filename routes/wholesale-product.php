<?php

use App\Http\Controllers\Admin\Addons\WholeSaleProductController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath','isInstalled']
    ], function () {
    Route::middleware(['adminCheck', 'loginCheck', 'XSS'])->prefix('admin')->group(function () {

        //Refunds Settings
        Route::group(['prefix' => 'wholesale'], function () {

            Route::get('/setting',[WholeSaleProductController::class,'setting'])->name('wholesale.setting')->middleware('PermissionCheck:product_read');
            Route::get('products/{status?}',[WholeSaleProductController::class,'wholesaleProducts'])->name('wholesale.products')->middleware('PermissionCheck:product_read');
            Route::get('product/create',[WholeSaleProductController::class,'create'])->name('wholesale.product.create')->middleware('PermissionCheck:product_create');
            Route::post('product/create',[WholeSaleProductController::class,'store'])->name('wholesale.product.create.post')->middleware('PermissionCheck:product_create');
            Route::get('edit-product/{id}',[WholeSaleProductController::class, 'edit'])->name('wholesale.product.edit')->middleware('PermissionCheck:product_update');
            Route::post('update-product',[WholeSaleProductController::class, 'update'])->name('wholesale.product.update')->middleware('PermissionCheck:product_update');
            Route::get('product-clone/{id}',[WholeSaleProductController::class, 'cloneWholesaleProduct'])->name('wholesale.product.clone')->middleware('PermissionCheck:product_clone');
            Route::post('clone-product',[WholeSaleProductController::class, 'storeCloneWholesaleProduct'])->name('wholesale.product.clone.store')->middleware('PermissionCheck:product_clone');
        });
    });
});
