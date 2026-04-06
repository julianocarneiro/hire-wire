<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('bank-accounts', [BankAccountController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('bank-accounts.store');
    Route::get('bank-accounts/{id}/movimentacoes', [BankAccountController::class, 'movements'])
        ->whereNumber('id')
        ->name('bank-accounts.movements');
    Route::get('bank-accounts/{id}', [BankAccountController::class, 'show'])
        ->whereNumber('id')
        ->name('bank-accounts.show');
    Route::patch('bank-accounts/{id}', [BankAccountController::class, 'update'])
        ->whereNumber('id')
        ->name('bank-accounts.update');
    Route::delete('bank-accounts/{id}', [BankAccountController::class, 'destroy'])
        ->whereNumber('id')
        ->name('bank-accounts.destroy');
});
