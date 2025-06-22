<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;

// Public routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/info', [AuthController::class, 'user']);
    Route::get('/user/profile', [AuthController::class, 'profile']);
    Route::middleware('role:admin')->group(function () {
        Route::get('/departments', [DepartmentController::class, 'departments']);
        Route::get('/departments/summary', [DepartmentController::class, 'summary']);
        Route::get('/departments/{id}', [DepartmentController::class, 'details']);
        Route::post('/departments', [DepartmentController::class, 'store']);
    });
});
