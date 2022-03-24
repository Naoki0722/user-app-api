<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\UsersController;

// Route::post('/register', [RegisterController::class, 'post']);
// Route::post('/logout', [LogoutController::class, 'post']);
// Route::get('/user', [UsersController::class, 'get']);
// Route::get('/user/all', [UsersController::class, 'all']);
// Route::get('/user/person', [UsersController::class, 'person']);
// Route::put('/user', [UsersController::class, 'put']);
// Route::delete('/user', [UsersController::class, 'delete']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::post('/login', [AuthController::class, 'login']);
