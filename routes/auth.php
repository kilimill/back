<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['as' => 'auth.'], function () {
    Route::post('/auth', [AuthController::class, 'login'])->name('login');
    Route::post('/input-private-code', [AuthController::class, 'inputPrivateCode'])->name('inputPrivateCode');
    Route::post('/logout', [AuthController::class, 'destroy'])
        ->middleware('auth')
        ->name('destroy');
});
