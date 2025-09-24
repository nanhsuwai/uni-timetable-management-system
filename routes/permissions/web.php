<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Permission\IndexController;
use App\Http\Controllers\Permission\CreateController;

Route::prefix('permissions')->name('permissions:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/permission/create', CreateController::class)->name('create');
});
