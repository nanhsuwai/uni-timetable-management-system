<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicLevel\IndexController;
use App\Http\Controllers\AcademicLevel\CreateController;
use App\Http\Controllers\AcademicLevel\UpdateController;
use App\Http\Controllers\AcademicLevel\DeleteController;
use App\Http\Controllers\AcademicLevel\StatusController;
use App\Http\Controllers\Section\CreateController as SectionCreateController;
use App\Http\Controllers\Section\UpdateController as SectionUpdateController;
use App\Http\Controllers\Section\DeleteController as SectionDeleteController;
use App\Http\Controllers\Section\StatusController as SectionStatusController;
use App\Http\Controllers\Classroom\IndexController as ClassroomIndexController;
use App\Http\Controllers\Classroom\CreateController as ClassroomCreateController;
use App\Http\Controllers\Classroom\UpdateController as ClassroomUpdateController;
use App\Http\Controllers\Classroom\DeleteController as ClassroomDeleteController;
use App\Http\Controllers\Classroom\StatusController as ClassroomStatusController;

Route::prefix('academic-level')->name('academic_level:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{academicLevel}', UpdateController::class)->name('update');
    Route::post('/toggle-status/{academicLevel}', [StatusController::class, 'toggle'])->name('toggle-status');
    Route::delete('/delete/{academicLevel}', DeleteController::class)->name('delete');

    // Section management for each academic level
    Route::prefix('{academicLevel}/sections')->name('sections.')->group(function () {
        Route::post('/create', SectionCreateController::class)->name('create');
        Route::post('/update/{section}', SectionUpdateController::class)->name('update');
        Route::post('/toggle-status/{section}', [SectionStatusController::class, 'toggle'])->name('toggle-status');
        Route::delete('/delete/{section}', SectionDeleteController::class)->name('delete');
    });

    // General section routes (for UI in academic level page)
    Route::post('/sections/create', SectionCreateController::class)->name('section.create');
    Route::post('/sections/update/{section}', SectionUpdateController::class)->name('section.update');
    Route::post('/sections/toggle-status/{section}', [SectionStatusController::class, 'toggle'])->name('section.toggle-status');
    Route::delete('/sections/delete/{section}', SectionDeleteController::class)->name('section.delete');

    // Classroom management for each academic level
    Route::prefix('{academicLevel}/classrooms')->name('classrooms.')->group(function () {
        Route::get('/', ClassroomIndexController::class)->name('all');
        Route::post('/create', ClassroomCreateController::class)->name('create');
        Route::post('/update/{classroom}', ClassroomUpdateController::class)->name('update');
        Route::post('/toggle-status/{classroom}', [ClassroomStatusController::class, 'toggle'])->name('toggle-status');
        Route::delete('/delete/{classroom}', ClassroomDeleteController::class)->name('delete');
    });
});
