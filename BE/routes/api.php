<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\MfaController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => response()->json(['status' => 'ok']));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/mfa/verify', [MfaController::class, 'verify']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/mfa/enable', [MfaController::class, 'enable']);
    Route::post('/mfa/confirm', [MfaController::class, 'confirm']);
    Route::post('/mfa/disable', [MfaController::class, 'disable']);
});