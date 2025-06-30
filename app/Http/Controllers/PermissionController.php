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
}