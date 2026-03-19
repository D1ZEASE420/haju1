<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\MarkerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [MarkerController::class, 'dashboard'])->name('dashboard');
});

Route::get('/weather', [WeatherController::class, 'index']);
Route::get('/weather/search', [WeatherController::class, 'search']);

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
