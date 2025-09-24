<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimeSlotTemplate\IndexController;
use App\Http\Controllers\TimeSlotTemplate\CreateController;
use App\Http\Controllers\TimeSlotTemplate\UpdateController;
use App\Http\Controllers\TimeSlotTemplate\DeleteController;

Route::prefix('time-slot-templates')->name('time-slot-template:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{template}', UpdateController::class)->name('update');
    Route::delete('/delete/{template}', DeleteController::class)->name('delete');
});