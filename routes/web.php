<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\MarkdownPreviewController;
use App\Http\Controllers\ProtocolController;
use App\Http\Controllers\ProtocolShareController;
use App\Http\Controllers\TimeTableController;
use App\Http\Controllers\UserLocaleController;
use App\Http\Controllers\WikiCategoryController;
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

// Public pages, reachable without logging in. The server's basic auth must let `share/` through.
Route::prefix('share')->middleware('throttle:60,1')->group(function () {
    Route::get('protocols/{token}', [ProtocolShareController::class, 'show'])->name('protocols.public');
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::put('user/locale', [UserLocaleController::class, 'update'])->name('user-locale.update');
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::post('markdown/preview', MarkdownPreviewController::class)->name('markdown.preview');

    Route::prefix('time-tables')->name('time-tables.')->group(function () {
        Route::post('course-count-per-week', [TimeTableController::class, 'courseCountPerWeek'])->name('course-count-per-week');
        Route::post('{time_table}/reimport', [TimeTableController::class, 'reimport'])->name('reimport');
    });
    Route::resource('time-tables', TimeTableController::class);
    Route::resource('time-tables.courses', CourseController::class)->only(['store', 'update', 'destroy']);

    Route::prefix('protocols')->name('protocols.')->group(function () {
        Route::get('{protocol}/download', [ProtocolController::class, 'download'])->name('download');

        Route::controller(ProtocolShareController::class)->group(function () {
            Route::post('{protocol}/share', 'store')->name('share');
            Route::delete('{protocol}/share', 'destroy')->name('unshare');
        });
    });
    Route::resource('protocols', ProtocolController::class);

    Route::resource('wiki-categories', WikiCategoryController::class)->only(['store', 'update', 'destroy']);
});
