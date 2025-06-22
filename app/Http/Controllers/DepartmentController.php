<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Http\Resources\DepartmentResource;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function departments(Request $request)
    {
        $departments = Department::with(['manager', 'employees'])->get();
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách phòng ban thành công',
            'data' => DepartmentResource::collection($departments),
        ]);
    }

    public function summary(Request $request)
    {
        $total_departments = \App\Models\Department::count();
        $total_employees = \App\Models\User::count();
        $avg_employees_per_department = $total_departments > 0 ? (int) round($total_employees / $total_departments) : 0;
        $largest_department = \App\Models\Department::withCount('employees')
            ->orderByDesc('employees_count')
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Lấy thông tin tổng quan thành công',
            'data' => [
                'total_departments' => $total_departments,
                'total_employees' => $total_employees,
                'avg_employees_per_department' => $avg_employees_per_department,
                'largest_department' => $largest_department ? [
                    'name' => $largest_department->name,
                    'employee_count' => $largest_department->employees_count,
                ] : null,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:active,inactive',
        ]);

        $department = \App\Models\Department::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tạo phòng ban thành công',
            'data' => new \App\Http\Resources\DepartmentResource($department->fresh(['manager', 'employees'])),
        ], 201);
    }
} 