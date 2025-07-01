<?php

namespace App\Http\Controllers;

use App\Http\Resources\ModulePermissionResource;
use App\Http\Resources\PermissionResource;
use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::with('modulePermission')->get();
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách quyền thành công',
            'data' => PermissionResource::collection($permissions),
        ]);
    }

    public function modulePermission()
    {
        $modulePermissions = ModulePermission::all();
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách module quyền thành công',
            'data' => ModulePermissionResource::collection($modulePermissions),
        ]);
    }

    public function syncPermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = \Spatie\Permission\Models\Role::findOrFail($request->role_id);
        if ($role->name === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thay đổi quyền của quản trị viên (admin)!'
            ], 400);
        }
        $role->syncPermissions($request->permission_ids ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật quyền cho role thành công!'
        ]);
    }

    public function syncPermissionByEmployee(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $user = \App\Models\User::findOrFail($request->employee_id);
        if ($user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thay đổi quyền trực tiếp của quản trị viên (admin)!'
            ], 400);
        }
        $user->syncPermissions($request->permission_ids ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật quyền cho nhân viên thành công!'
        ]);
    }
}