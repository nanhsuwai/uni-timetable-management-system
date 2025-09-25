<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Section\IndexController;
use App\Http\Controllers\Section\CreateController;
use App\Http\Controllers\Section\UpdateController;
use App\Http\Controllers\Section\DeleteController;
use App\Http\Controllers\Section\StatusController;

Route::prefix('section')->name('section:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{section}', UpdateController::class)->name('update');
    Route::post('/toggle-status/{section}', [StatusController::class, 'toggle'])->name('toggle-status');
    Route::delete('/delete/{section}', DeleteController::class)->name('delete');
});
