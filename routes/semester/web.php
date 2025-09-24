<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Semester\IndexController;
use App\Http\Controllers\Semester\CreateController;
use App\Http\Controllers\Semester\UpdateController;
use App\Http\Controllers\Semester\DeleteController;

Route::prefix('semester')->name('semester:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{semester}', UpdateController::class)->name('update');
    Route::delete('/delete/{semester}', DeleteController::class)->name('delete');
});
