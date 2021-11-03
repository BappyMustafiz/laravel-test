<?php

use App\Http\Controllers\User\AuthController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], 'confirm-invitation/{verification_token}', [AuthController::class, 'confirmInvitation']);
Route::post('verify-pin', [AuthController::class, 'verifyPin']);

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:user')->group(function () {

    Route::post('update-profile/{id}', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);
});
