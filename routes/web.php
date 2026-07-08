<?php

declare(strict_types=1);

Illuminate\Support\Facades\Route::get('/', fn () => inertia('index'));

Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index']);
Route::get('/blog/{article:slug}', [App\Http\Controllers\BlogController::class, 'show']);

Route::get('/products/{product}', [App\Http\Controllers\ProductController::class, 'show'])->name('product.show');
Route::get('/sets/{set}', [App\Http\Controllers\SetController::class, 'show'])->name('sets.show');

Route::post('/cart/items', [App\Http\Controllers\CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{product}', [App\Http\Controllers\CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{product}', [App\Http\Controllers\CartController::class, 'destroy'])->name('cart.items.destroy');
