<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\EditController;
use App\Http\Controllers\User\IndexController;
use App\Http\Controllers\User\CreateController;
use App\Http\Controllers\User\CreateUserController;
use App\Http\Controllers\User\DeleteUserController;
use App\Http\Controllers\User\UpdatePasswordController;
use App\Http\Controllers\User\CreatePermissionController;

Route::prefix('users')->name('users:')->group(function () {
    Route::get('/', IndexController::class)->name('all');
    Route::post('/create', CreateController::class)->name('create');
    Route::post('/update/{user}', EditController::class)->name('update');
    Route::post('/user/create', CreateUserController::class)->name("user:create");
    Route::post('/create-permission/{user}', CreatePermissionController::class)->name('create:permission');
    Route::put('/password/update/{user}', UpdatePasswordController::class)->name('update:password');
    Route::delete('/delete/{user}', DeleteUserController::class)->name('delete');
});
