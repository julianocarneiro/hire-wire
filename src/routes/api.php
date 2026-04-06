<?php

use App\Http\Controllers\Api\CurrentUserProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CheckToken;

Route::get('/me', CurrentUserProfileController::class)->middleware([
    'auth:api',
    CheckToken::using('read:profile'),
]);
