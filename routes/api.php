<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EvaluationCriteriaController;
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

        //Task
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/', [TaskController::class, 'store']);
            Route::post('/{id}', [TaskController::class, 'update']);
            // Route::delete('/{id}', [TaskController::class, 'destroy']);
            
            // Task Progress History
            Route::post('/{taskId}/progress', [TaskProgressController::class, 'store']);
            Route::delete('/{taskId}/files/{id}', [TaskController::class, 'deleteFile']);
            // Route::delete('/{taskId}/progress/{progressId}', [TaskProgressController::class, 'destroy']);
        });

        // Selection
        Route::get('departments/select', [DepartmentController::class, 'select']);
        Route::get('employees/all-employees', [EmployeeController::class, 'allEmployees']);
        Route::get('/activity-log', [ActivityLogController::class, 'index']);

        Route::group(['prefix' => 'evaluations'], function () {
            Route::get('/', [EvaluationController::class, 'index']);
            Route::get('/{id}', [EvaluationController::class, 'show']);
        });
    });





    // Admin
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

        // Evaluation Criteria
        Route::prefix('evaluation-criteria')->group(function () {
            Route::get('/', [EvaluationCriteriaController::class, 'index']);
            Route::post('/category', [EvaluationCriteriaController::class, 'storeCategory']);
            Route::put('/category/{id}', [EvaluationCriteriaController::class, 'updateCategory']);
            Route::delete('/category/{id}', [EvaluationCriteriaController::class, 'destroyCategory']);
            Route::post('/criteria', [EvaluationCriteriaController::class, 'storeCriteria']);
            Route::put('/criteria/{id}', [EvaluationCriteriaController::class, 'updateCriteria']);
            Route::delete('/criteria/{id}', [EvaluationCriteriaController::class, 'destroyCriteria']);
            // Route::get('/{id}', [EvaluationCriteriaController::class, 'show']);
            // Route::post('/', [EvaluationCriteriaController::class, 'store']);
            // Route::put('/{id}', [EvaluationCriteriaController::class, 'update']);
        });
    });
});
