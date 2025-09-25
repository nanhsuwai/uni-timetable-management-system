<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicYear\IndexController;
use App\Http\Controllers\AcademicYear\CreateController;
use App\Http\Controllers\AcademicYear\UpdateController;
use App\Http\Controllers\AcademicYear\DeleteController;
use App\Http\Controllers\AcademicYear\ProgramController;
use App\Http\Controllers\AcademicYear\StatusController;
use App\Http\Controllers\Semester\CreateController as SemesterCreateController;
use App\Http\Controllers\Semester\UpdateController as SemesterUpdateController;
use App\Http\Controllers\Semester\DeleteController as SemesterDeleteController;
use App\Http\Controllers\Semester\StatusController as SemesterStatusController;

Route::prefix('academic-year')->name('academic-year:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{academicYear}', UpdateController::class)->name('update');
    Route::delete('/delete/{academicYear}', DeleteController::class)->name('delete');
    Route::post('/{academicYear}/toggle-status', [StatusController::class, 'toggle'])->name('toggle-status');

      Route::get('{academicYear}/programs', [ProgramController::class, 'index'])->name('programs');
      Route::post('{academicYear}/programs/create', [ProgramController::class, 'create'])->name('programs:create');

    Route::prefix('{academicYear}/semesters')->name('semesters:')->group(function () {
        Route::post('/create', SemesterCreateController::class)->name('create');
        Route::post('/update/{semester}', SemesterUpdateController::class)->name('update');
        Route::delete('/delete/{semester}', SemesterDeleteController::class)->name('delete');
        Route::post('/toggle-status/{semester}', [SemesterStatusController::class, 'toggle'])->name('toggle-status');
    });
});
