<?php

use App\Http\Controllers\AlbumApiController;
use Illuminate\Support\Facades\Route;


Route::prefix('albums')->name('api.albums.')->group(function () {
    Route::get('/',            [AlbumApiController::class, 'index'])->name('index');
    Route::get('/meta/genres', [AlbumApiController::class, 'genres'])->name('genres');
    Route::get('/meta/stats',  [AlbumApiController::class, 'stats'])->name('stats');
    Route::get('/{id}',        [AlbumApiController::class, 'show'])->name('show');
});