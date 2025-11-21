<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [App\Http\Controllers\WishboxController::class, 'index'])
    ->name('wishbox');
Route::post('/wishbox',
    [App\Http\Controllers\WishboxController::class, 'store'])
    ->name('wishbox.store');

Route::get('/wishlist',
    [App\Http\Controllers\WishlistController::class, 'index'])
    ->name('wishlist');

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('index');
    Route::post('/wishes', [App\Http\Controllers\WishboxController::class, 'store'])->name('wishes.store');
    Route::patch('/wishes/{wish}/toggle', [App\Http\Controllers\WishboxController::class, 'togglePublic'])->name('wishes.toggle');
    Route::delete('/wishes/{wish}', [App\Http\Controllers\WishboxController::class, 'destroy'])->name('wishes.destroy');
});

require __DIR__.'/auth.php';

