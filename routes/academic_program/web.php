<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicProgram\IndexController;
use App\Http\Controllers\AcademicProgram\CreateController;
use App\Http\Controllers\AcademicProgram\UpdateController;
use App\Http\Controllers\AcademicProgram\DeleteController;

Route::prefix('academic-program')->name('academic_program:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{academicProgram}', UpdateController::class)->name('update');
    Route::delete('/delete/{academicProgram}', DeleteController::class)->name('delete');
});
