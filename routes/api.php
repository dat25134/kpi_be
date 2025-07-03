<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskProgressController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::group([], function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user/info', [AuthController::class, 'user']);
        Route::get('/user/profile', [AuthController::class, 'profile']);

        // Category
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
        });

        // Employee
        Route::prefix('employees')->group(function () {
            Route::get('/all-employees', [EmployeeController::class, 'allEmployees']);
        });

        //Task
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/', [TaskController::class, 'store']);
            Route::put('/{id}', [TaskController::class, 'update']);
            // Route::delete('/{id}', [TaskController::class, 'destroy']);
            
            // Task Progress History
            Route::post('/{taskId}/progress', [TaskProgressController::class, 'store']);
            // Route::delete('/{taskId}/progress/{progressId}', [TaskProgressController::class, 'destroy']);
        });
    });
    // Admin
    Route::middleware('role:admin')->group(function () {

        // Department
        Route::group(['prefix' => 'departments'], function () {
            Route::get('/select', [DepartmentController::class, 'select']);
            Route::get('/', [DepartmentController::class, 'departments']);
            Route::get('/summary', [DepartmentController::class, 'summary']);
            Route::get('/{id}', [DepartmentController::class, 'details']);
            Route::post('/', [DepartmentController::class, 'store']);
            Route::put('/{id}', [DepartmentController::class, 'update']);
            Route::delete('/{id}', [DepartmentController::class, 'destroy']);
        });

        // Employee
        Route::group(['prefix' => 'employees'], function () {
            Route::get('/manager', [EmployeeController::class, 'manager']);
            Route::get('/', [EmployeeController::class, 'employees']);
            Route::get('/summary', [EmployeeController::class, 'summary']);
            Route::post('/', [EmployeeController::class, 'store']);
            Route::get('/{id}', [EmployeeController::class, 'details']);
            Route::put('/{id}', [EmployeeController::class, 'update']);
            Route::delete('/{id}', [EmployeeController::class, 'destroy']);
        });

        // Role management
        Route::prefix('roles')->group(function () {
            Route::put('/reorder', [RoleController::class, 'reorder']); // Sắp xếp
            Route::get('/selection', [RoleController::class, 'selection']);
            Route::get('/summary', [RoleController::class, 'summary']);
            Route::get('/', [RoleController::class, 'index']);          // Danh sách
            Route::get('/{id}', [RoleController::class, 'show']);       // Chi tiết
            Route::post('/', [RoleController::class, 'store']);         // Thêm mới
            Route::put('/{id}', [RoleController::class, 'update']);     // Cập nhật
            Route::delete('/{id}', [RoleController::class, 'destroy']); // Xóa
        });

        // Permission management
        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::get('/permission-modules', [PermissionController::class, 'modulePermission']);
            Route::post('/sync-permission', [PermissionController::class, 'syncPermission']);
            Route::post('/sync-permission-by-employee', [PermissionController::class, 'syncPermissionByEmployee']);
        });
    });
});
