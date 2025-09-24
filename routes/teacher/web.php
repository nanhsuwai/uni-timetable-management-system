<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\IndexController;
use App\Http\Controllers\Teacher\CreateController;
use App\Http\Controllers\Teacher\UpdateController;
use App\Http\Controllers\Teacher\DeleteController;

Route::prefix('teacher')->name('teacher:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{teacher}', UpdateController::class)->name('update');
    Route::delete('/delete/{teacher}', DeleteController::class)->name('delete');
});
