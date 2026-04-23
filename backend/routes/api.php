<?php

use App\Http\Controllers\Api\AdminUserApiController;
use App\Http\Controllers\Api\AdminCarApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CarApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
	Route::post('/login', [AuthApiController::class, 'login']);

	Route::middleware('api.token')->group(function (): void {
		Route::get('/me', [AuthApiController::class, 'me']);
		Route::post('/logout', [AuthApiController::class, 'logout']);
	});
});

Route::middleware('api.token')->group(function (): void {
	Route::get('/cars', [CarApiController::class, 'index']);
	Route::post('/cars/{car}/reserve', [CarApiController::class, 'reserve']);
	Route::post('/cars/{car}/complete', [CarApiController::class, 'complete']);
});

Route::middleware('api.token')->prefix('admin')->group(function (): void {
	Route::get('/users', [AdminUserApiController::class, 'index']);
	Route::post('/users', [AdminUserApiController::class, 'store']);
	Route::delete('/users/{user}', [AdminUserApiController::class, 'destroy']);
	Route::get('/cars', [AdminCarApiController::class, 'index']);
	Route::post('/cars', [AdminCarApiController::class, 'store']);
	Route::put('/cars/{car}', [AdminCarApiController::class, 'update']);
	Route::delete('/cars/{car}', [AdminCarApiController::class, 'destroy']);
});
