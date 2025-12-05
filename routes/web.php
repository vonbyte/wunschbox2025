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
Route::get('/wishlist/{wish}',
    [App\Http\Controllers\WishlistController::class, 'show'])->name('wish.show');

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('index');
    Route::post('/wishes', [App\Http\Controllers\AdminController::class, 'store'])->name('wishes.store');
    Route::get('/wishes/{wish}', [App\Http\Controllers\AdminController::class, 'edit'])->name('wishes.edit');
    Route::patch('/wishes/{wish}', [App\Http\Controllers\AdminController::class, 'update'])->name('wishes.update');
    Route::delete('/wishes/{wish}', [App\Http\Controllers\AdminController::class, 'destroy'])->name('wishes.destroy');

    Route::patch('/wishes/{wish}/toggle', [App\Http\Controllers\AdminController::class, 'togglePublic'])->name('wishes.toggle');
    Route::patch('/wishes/{wish}/status', [App\Http\Controllers\AdminController::class, 'updateStatus'])->name('wishes.update-status');
    Route::patch('/wishes/{wish}/sort', [App\Http\Controllers\AdminController::class, 'updateSortNr'])->name('wishes.update-sort');

});

require __DIR__.'/auth.php';
