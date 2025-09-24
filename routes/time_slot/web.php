<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimeSlot\IndexController;
use App\Http\Controllers\TimeSlot\CreateController;
use App\Http\Controllers\TimeSlot\UpdateController;
use App\Http\Controllers\TimeSlot\DeleteController;
use App\Http\Controllers\TimeSlot\GenerateController;

Route::prefix('time-slot')->name('time-slot:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{timeSlot}', UpdateController::class)->name('update');
    Route::delete('/delete/{timeSlot}', DeleteController::class)->name('delete');
    Route::post('/generate', GenerateController::class)->name('generate');
});
