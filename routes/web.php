<?php

use App\Http\Controllers\FridayController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FridayController::class, 'home'])->name('home');
Route::get('/api/friday-time', [FridayController::class, 'apiFridayTime'])->name('api.friday-time');
Route::get('/widget/{city}', [FridayController::class, 'widget'])->name('widget');
Route::get('/{citySlug}', [FridayController::class, 'city'])
    ->where('citySlug', '[a-z\-]+-cuma-saati')
    ->name('city');

Route::prefix('{locale}')->where(['locale' => 'en|tr'])->group(function () {
    Route::get('/', [FridayController::class, 'home'])->name('locale.home');
    Route::get('/api/friday-time', [FridayController::class, 'apiFridayTime'])->name('locale.api.friday-time');
    Route::get('/widget/{city}', [FridayController::class, 'widget'])->name('locale.widget');
    Route::get('/{citySlug}', [FridayController::class, 'city'])
        ->where('citySlug', '[a-z\-]+-friday-time|[a-z\-]+-cuma-saati')
        ->name('locale.city');
});
