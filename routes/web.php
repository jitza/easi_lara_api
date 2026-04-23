<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/microsoft/redirect', [AuthController::class, 'redirectToMicrosoft']);

Route::get('/auth/microsoft/callback', [AuthController::class, 'handleMicrosoftCallback']);