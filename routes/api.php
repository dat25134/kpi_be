<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;

// Public routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/info', [AuthController::class, 'user']);
    Route::get('/user/profile', [AuthController::class, 'profile']);
    Route::middleware('role:admin')->group(function () {

        // Department
        Route::group(['prefix' => 'departments'], function () {
            Route::get('/', [DepartmentController::class, 'departments']);
            Route::get('/summary', [DepartmentController::class, 'summary']);
            Route::get('/{id}', [DepartmentController::class, 'details']);
            Route::post('/', [DepartmentController::class, 'store']);
            Route::put('/{id}', [DepartmentController::class, 'update']);
            Route::delete('/{id}', [DepartmentController::class, 'destroy']);
        });

        // Employee
        Route::group(['prefix' => 'employees'], function () {
            Route::get('/director', [EmployeeController::class, 'director']);
            Route::get('/', [EmployeeController::class, 'employees']);
            Route::get('/summary', [EmployeeController::class, 'summary']);
            Route::post('/', [EmployeeController::class, 'store']);
            Route::get('/{id}', [EmployeeController::class, 'details']);
            Route::put('/{id}', [EmployeeController::class, 'update']);
            Route::delete('/{id}', [EmployeeController::class, 'destroy']);

        });
    });
});
