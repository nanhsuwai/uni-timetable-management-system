<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicProgram\IndexController;
use App\Http\Controllers\AcademicProgram\CreateController;
use App\Http\Controllers\AcademicProgram\UpdateController;
use App\Http\Controllers\AcademicProgram\DeleteController;
use App\Http\Controllers\AcademicProgram\StatusController;

Route::prefix('academic-program')->name('academic_program:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{academicProgram}', UpdateController::class)->name('update');
    Route::post('/toggle-status/{academicProgram}', [StatusController::class, 'toggle'])->name('toggle-status');
    Route::delete('/delete/{academicProgram}', DeleteController::class)->name('delete');
});
