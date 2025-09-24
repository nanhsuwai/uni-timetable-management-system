<?php

use App\Events\UserInfoEvent;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\ProfileController;
use App\Models\Permission;
use App\Models\User;
use App\Notifications\Courses\CourseApplied;
use Illuminate\Foundation\Application;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\IndexController;
use App\Http\Controllers\WelcomeController;
use App\Notifications\Register\RegisterMember;



Route::get('/', WelcomeController::class)->middleware('guest')->name('welcome');


Route::get('/dashboard', IndexController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';

Route::middleware('auth')->group(function() {
    require __DIR__.'/user/web.php';
    require __DIR__.'/permissions/web.php';
    require __DIR__.'/academic_year/web.php';
    require __DIR__.'/semester/web.php';
    require __DIR__.'/section/web.php';
    require __DIR__.'/academic_program/web.php';
    require __DIR__.'/academic_level/web.php';
    require __DIR__.'/classroom/web.php';
    require __DIR__.'/timetable_entry/web.php';
    require __DIR__.'/teacher/web.php';
    require __DIR__.'/subject/web.php';
    require __DIR__.'/time_slot/web.php';
    require __DIR__.'/time_slot_template/web.php';
});









Route::get('send-mail', function() {
    try {
        $user = User::first();
        $user->notify(new RegisterMember($user, env('USER_PASSWORD')));
        dd('hey success');
    } catch (\Throwable $th) {
        throw $th;
    }
});




function getNearestDate($dateList) {
    // Convert dates in the list to Carbon objects
    $carbonList = array_map(function ($dateStr) {
        return Carbon::parse($dateStr);
    }, $dateList);

    // Get the current Carbon object
    $currentCarbon = Carbon::now();

    // Calculate the difference between the current Carbon object and each Carbon object in the list
    $timeDiffs = array_map(function ($carbonObj) use ($currentCarbon) {
        return $currentCarbon->diffInSeconds($carbonObj, false);
    }, $carbonList);

    // Find the smallest positive time difference, which represents the nearest date
    $minTimeDiff = min($timeDiffs, function ($timeDiff) {
        return ($timeDiff >= 0) ? $timeDiff : PHP_INT_MAX;
    });

    // Return the original date corresponding to the nearest Carbon object
    $nearestDateIndex = array_search($minTimeDiff, $timeDiffs);
    return $dateList[$nearestDateIndex];
}
