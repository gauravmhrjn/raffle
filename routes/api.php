<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RaffleEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index'])->name('product.list');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('product.show');

Route::post('/login', [AuthController::class, 'login'])->name('auth.loging');

Route::middleware(['auth:sanctum', 'throttle:6,1'])
    ->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::post('/raffle/entry', [RaffleEntryController::class, 'store'])->name('raffle.entry.store');
        Route::delete('/raffle/entry/delete', [RaffleEntryController::class, 'destroy'])->name('raffle.entry.delete');
    });
