<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicYear\IndexController;
use App\Http\Controllers\AcademicYear\CreateController;
use App\Http\Controllers\AcademicYear\UpdateController;
use App\Http\Controllers\AcademicYear\DeleteController;
use App\Http\Controllers\AcademicYear\StatusController;

Route::prefix('academic-year')->name('academic-year:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{academicYear}', UpdateController::class)->name('update');
    Route::delete('/delete/{academicYear}', DeleteController::class)->name('delete');
    Route::post('/{academicYear}/toggle-status', [StatusController::class, 'toggle'])->name('toggle-status');
});
