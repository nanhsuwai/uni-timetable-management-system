<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Subject\IndexController;
use App\Http\Controllers\Subject\CreateController;
use App\Http\Controllers\Subject\UpdateController;
use App\Http\Controllers\Subject\DeleteController;
use App\Http\Controllers\Subject\AssignTeacherController;

Route::prefix('subject')->name('subject:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{subject}', UpdateController::class)->name('update');
    Route::delete('/delete/{subject}', DeleteController::class)->name('delete');

    // Teacher assignment routes
    Route::get('/{subject}/assign-teacher', [AssignTeacherController::class, 'show'])->name('assign-teacher');
    Route::post('/{subject}/assign-teacher', [AssignTeacherController::class, 'assign'])->name('assign-teacher.store');
    Route::delete('/{subject}/remove-teacher/{teacher}', [AssignTeacherController::class, 'removeTeacher'])->name('remove-teacher');
    Route::get('/{subject}/assigned-teachers', [AssignTeacherController::class, 'getAssignedTeachers'])->name('assigned-teachers');
});
