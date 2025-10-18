<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimetableEntry\IndexController;
use App\Http\Controllers\TimetableEntry\CreateController;
use App\Http\Controllers\TimetableEntry\UpdateController;
use App\Http\Controllers\TimetableEntry\DeleteController;
use App\Http\Controllers\TimetableEntry\GridViewController;
use App\Http\Controllers\TimetableEntry\GenerateTimetableController;
use App\Http\Controllers\TimetableEntry\ExportController;

Route::prefix('timetable-entry')->name('timetable_entry.')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::get('/grid', GridViewController::class)->name('grid');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{timetableEntry}', UpdateController::class)->name('update');
    Route::delete('/delete/{timetableEntry}', DeleteController::class)->name('delete');
    Route::get('/generate', GenerateTimetableController::class)->name('generate');

    // ✅ Excel Export Route (corrected)
    Route::get('/timetable/export', [ExportController::class, 'exportExcel'])
        ->name('export');
});
