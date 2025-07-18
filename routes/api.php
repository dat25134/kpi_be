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
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Public selection routes
Route::get('departments/select', [DepartmentController::class, 'select']);
Route::get('employees/all-employees', [EmployeeController::class, 'allEmployees']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('roles/selection', [RoleController::class, 'selection']);
Route::get('permissions', [PermissionController::class, 'index']);
Route::get('permissions/permission-modules', [PermissionController::class, 'modulePermission']);
Route::get('employees/manager', [EmployeeController::class, 'manager']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user/info', [AuthController::class, 'user']);
    Route::get('/user/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Task (project.* permissions)
    Route::prefix('tasks')->group(function () {
        Route::middleware('permission:project.view_all|project.view_related')->get('/current-user-work-descriptions', [EvaluationController::class, 'getCurrentUserWorkDescriptions']);
        Route::middleware('permission:project.view_all|project.view_related')->get('/', [TaskController::class, 'index']);
        Route::middleware('permission:project.create')->post('/', [TaskController::class, 'store']);
        Route::middleware('permission:project.edit')->post('/{id}', [TaskController::class, 'update']);
        // Route::delete('/{id}', [TaskController::class, 'destroy']);
        Route::middleware('permission:project.update_progress')->post('/{taskId}/progress', [TaskProgressController::class, 'store']);
        Route::middleware('permission:project.edit')->delete('/{taskId}/files/{id}', [TaskController::class, 'deleteFile']);
        // Route::delete('/{taskId}/progress/{progressId}', [TaskProgressController::class, 'destroy']);       
    });

    // Activity log (report.view_log)
    Route::middleware('permission:system.view_log')->get('/activity-log', [ActivityLogController::class, 'index']);

    // Evaluation (evaluation.* permissions)
    Route::prefix('evaluations')->group(function () {
        Route::middleware('permission:evaluation.view')->get('/', [EvaluationController::class, 'index']);
        Route::middleware('permission:evaluation.view')->get('/{id}', [EvaluationController::class, 'show']);
        Route::middleware('permission:evaluation.save|evaluation.approve')->post('/{id}/save', [EvaluationController::class, 'save']);
        Route::middleware('permission:evaluation.approve')->put('/{id}/work-descriptions', [EvaluationController::class, 'updateWorkDescriptions']);
        Route::middleware('permission:evaluation.view')->delete('/{id}', [EvaluationController::class, 'destroy']);
        Route::middleware('permission:evaluation.view')->post('/manual-create-evaluation', [EvaluationController::class, 'manualCreateEvaluation']);
    });

    // Department (department.* permissions)
    Route::prefix('departments')->group(function () {
        Route::middleware('permission:department.manage')->get('/', [DepartmentController::class, 'departments']);
        Route::middleware('permission:department.manage')->get('/summary', [DepartmentController::class, 'summary']);
        Route::middleware('permission:department.manage')->get('/{id}', [DepartmentController::class, 'details']);
        Route::middleware('permission:department.manage')->post('/', [DepartmentController::class, 'store']);
        Route::middleware('permission:department.manage')->put('/{id}', [DepartmentController::class, 'update']);
        Route::middleware('permission:department.manage')->delete('/{id}', [DepartmentController::class, 'destroy']);
        Route::middleware('permission:department.manage')->post('/{id}/assign-manager', [DepartmentController::class, 'assignManager']);
    });

    // Employee (hr.* permissions)
    Route::prefix('employees')->group(function () {
        Route::middleware('permission:hr.view')->get('/', [EmployeeController::class, 'employees']);
        Route::middleware('permission:hr.view')->get('/summary', [EmployeeController::class, 'summary']);
        Route::middleware('permission:hr.create')->post('/', [EmployeeController::class, 'store']);
        Route::middleware('permission:hr.view')->get('/{id}', [EmployeeController::class, 'details']);
        Route::middleware('permission:hr.edit')->put('/{id}', [EmployeeController::class, 'update']);
        Route::middleware('permission:hr.delete')->delete('/{id}', [EmployeeController::class, 'destroy']);
    });

    // Role management (system.grant_permission)
    Route::prefix('roles')->middleware('permission:system.grant_permission')->group(function () {
        Route::put('/reorder', [RoleController::class, 'reorder']);
        Route::get('/summary', [RoleController::class, 'summary']);
        Route::get('/', [RoleController::class, 'index']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
    });

    // Permission management (system.grant_permission)
    Route::prefix('permissions')->middleware('permission:system.grant_permission')->group(function () {
        Route::post('/sync-permission', [PermissionController::class, 'syncPermission']);
        Route::post('/sync-permission-by-employee', [PermissionController::class, 'syncPermissionByEmployee']);
    });

    // Evaluation Criteria (evaluation_criteria.manage permission)
    Route::prefix('evaluation-criteria')->middleware('permission:evaluation_criteria.manage')->group(function () {
        Route::get('/', [EvaluationCriteriaController::class, 'index']);
        Route::post('/category', [EvaluationCriteriaController::class, 'storeCategory']);
        Route::put('/category/{id}', [EvaluationCriteriaController::class, 'updateCategory']);
        Route::delete('/category/{id}', [EvaluationCriteriaController::class, 'destroyCategory']);
        Route::post('/criteria', [EvaluationCriteriaController::class, 'storeCriteria']);
        Route::put('/criteria/{id}', [EvaluationCriteriaController::class, 'updateCriteria']);
        Route::delete('/criteria/{id}', [EvaluationCriteriaController::class, 'destroyCriteria']);
    });

    // Report (report.* permissions)
    Route::prefix('report')->group(function () {
        Route::middleware('permission:report.view_dashboard')->get('/overview', [ReportController::class, 'overview']);
        Route::middleware('permission:report.view_dashboard')->get('/department-stats', [ReportController::class, 'departmentStats']);
        Route::middleware('permission:report.view_dashboard')->get('/position-stats', [ReportController::class, 'positionStats']);
        Route::middleware('permission:report.view_dashboard')->get('/task-progress', [ReportController::class, 'taskProgress']);
        Route::middleware('permission:report.view_dashboard')->get('/kpi-trends', [ReportController::class, 'kpiTrends']);
    });
});
