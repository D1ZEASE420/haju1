<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\AlbumController;

// ------------------------------------------------------------------
// Pood (avalik — vaatamine ei nõua sisselogimist)
// ------------------------------------------------------------------
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');

// ------------------------------------------------------------------
// Ostukorv (avalik — session-põhine, ei nõua autentimist)
// ------------------------------------------------------------------
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',              [CartController::class, 'index'])->name('index');
    Route::post('/add',          [CartController::class, 'add'])->name('add');
    Route::patch('/{productId}', [CartController::class, 'update'])->name('update');
    Route::delete('/{productId}',[CartController::class, 'remove'])->name('remove');
    Route::delete('/',           [CartController::class, 'clear'])->name('clear');
});

// ------------------------------------------------------------------
// Checkout + tellimused (nõuab sisselogimist)
// ------------------------------------------------------------------
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/checkout',                    [ShopController::class,  'checkout'])->name('checkout');
    Route::post('/orders/payment-intent',      [OrderController::class, 'createPaymentIntent'])->name('orders.payment-intent');
    Route::post('/orders',                     [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->name('orders.confirmation');
});

// ------------------------------------------------------------------
// Stripe Webhook (välja arvata CSRF-kaitsest — vt bootstrap/app.php)
// ------------------------------------------------------------------
Route::post('/stripe/webhook', [OrderController::class, 'webhook'])->name('stripe.webhook');

// ------------------------------------------------------------------
// Authenticated routes
// ------------------------------------------------------------------
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Marker management
    Route::get('/map/markers',            [MarkerController::class, 'index']);
    Route::post('/map/marker',            [MarkerController::class, 'store']);
    Route::patch('/map/marker/{marker}',  [MarkerController::class, 'update']);
    Route::delete('/map/marker/{marker}', [MarkerController::class, 'destroy']);

    // Blog CRUD
    Route::resource('blogs', BlogController::class);

    // Comments
    Route::post('blogs/{blog}/comments', [CommentController::class, 'store'])->name('blogs.comments.store');
    Route::patch('comments/{comment}',   [CommentController::class, 'update'])->name('comments.update');
    Route::delete('comments/{comment}',  [CommentController::class, 'destroy'])->name('comments.destroy');

    // Music albums
    Route::resource('music', AlbumController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});

// ------------------------------------------------------------------
// Public routes
// ------------------------------------------------------------------
Route::get('/weather',        [WeatherController::class, 'index']);
Route::get('/weather/search', [WeatherController::class, 'search']);

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

require __DIR__.'/settings.php';