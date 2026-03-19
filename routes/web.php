<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CommentController;

// --- Authenticated routes ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Marker management
    Route::get('/map/markers', [MarkerController::class, 'index']);           // fetch all
    Route::post('/map/marker', [MarkerController::class, 'store']);          // add new
    Route::patch('/map/marker/{marker}', [MarkerController::class, 'update']);   // edit
    Route::delete('/map/marker/{marker}', [MarkerController::class, 'destroy']); // delete

    // Blog CRUD
    Route::resource('blogs', BlogController::class);

    // Comments on blog posts
    Route::post('blogs/{blog}/comments', [CommentController::class, 'store'])
        ->name('blogs.comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');
});

// --- Public routes ---
Route::get('/weather', [WeatherController::class, 'index']);
Route::get('/weather/search', [WeatherController::class, 'search']);

// Home page
Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

require __DIR__.'/settings.php';