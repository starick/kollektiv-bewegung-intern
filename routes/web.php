<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\TimeTableController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::post('time-tables/course-count-per-week', [TimeTableController::class, 'courseCountPerWeek'])->name('time-tables.course-count-per-week');
    Route::resource('time-tables', TimeTableController::class);
    Route::post('time-tables/{time_table}/reimport', [TimeTableController::class, 'reimport'])->name('time-tables.reimport');
    Route::resource('time-tables.courses', CourseController::class)->only(['store', 'update', 'destroy']);
});