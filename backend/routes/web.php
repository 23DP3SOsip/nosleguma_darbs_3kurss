<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('sakums')
        : redirect()->route('pieslegties');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/pieslegties', [AuthController::class, 'showLoginForm'])->name('pieslegties');
    Route::post('/pieslegties', [AuthController::class, 'login'])->name('pieslegties.izpilde');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/iziet', [AuthController::class, 'logout'])->name('iziet');
    Route::get('/sakums', [HomeController::class, 'index'])->name('sakums');

    Route::middleware('role:admin,vadiba')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [UserManagementController::class, 'index'])->name('panelis');
        Route::post('/lietotaji', [UserManagementController::class, 'store'])->name('lietotaji.izveidot');
        Route::delete('/lietotaji/{user}', [UserManagementController::class, 'destroy'])->name('lietotaji.dzest');
    });
});
