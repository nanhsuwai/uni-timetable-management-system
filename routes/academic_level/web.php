<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicLevel\IndexController;
use App\Http\Controllers\AcademicLevel\CreateController;
use App\Http\Controllers\AcademicLevel\UpdateController;
use App\Http\Controllers\AcademicLevel\DeleteController;

Route::prefix('academic-level')->name('academic_level:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{academicLevel}', UpdateController::class)->name('update');
    Route::delete('/delete/{academicLevel}', DeleteController::class)->name('delete');
});
