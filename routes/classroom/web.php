<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Classroom\IndexController;
use App\Http\Controllers\Classroom\CreateController;
use App\Http\Controllers\Classroom\UpdateController;
use App\Http\Controllers\Classroom\DeleteController;

Route::prefix('classroom')->name('classroom:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{classroom}', UpdateController::class)->name('update');
    Route::delete('/delete/{classroom}', DeleteController::class)->name('delete');
});
