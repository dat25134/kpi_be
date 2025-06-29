<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Resources\RoleResource;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleSelectionResource;
use App\Http\Resources\RoleSummaryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::orderBy('order', 'asc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách vị trí thành công',
            'data' => RoleResource::collection($roles),
        ]);
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return new RoleResource($role);
    }

    public function store(StoreRoleRequest $request)
    {
        $data = $request->all();
        $data['color'] = $request->color ?? Role::generateUniqueColor();
        $data['order'] = Role::max('order') + 1;
        $data['name'] = Str::slug($data['display_name'], '');
        $data['guard_name'] = 'web';
        $role = Role::create($data);
        return response()->json([
            'status' => true,
            'message' => 'Thêm vị trí thành công',
            'data' => new RoleResource($role),
        ]);
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update($request->validated());
        return new RoleResource($role);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if ($role->name == 'admin') {   
            return response()->json(['message' => 'Không thể xóa vị trí admin'], 400);
        }
        $role->delete();
        return response()->json([
            'status' => true,
            'message' => 'Xóa vị trí thành công',
        ]);
    }

    public function selection()
    {
        $roles = Role::where('status', 'active')->get(['id', 'name', 'display_name']);
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách role thành công',
            'data' => RoleSelectionResource::collection($roles),
        ]);
    }

    public function summary()
    {
        $totalRoles = Role::count();
        $activeRoles = Role::where('status', 'active')->count();
        $inactiveRoles = Role::where('status', 'inactive')->count();
        $totalEmployees = \App\Models\User::count();
        $highestRole = Role::orderBy('order', 'asc')->first();

        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê vị trí thành công',
            'data' => [
                'totalRoles' => $totalRoles,
                'activeRoles' => $activeRoles,
                'inactiveRoles' => $inactiveRoles,
                'totalEmployees' => $totalEmployees,
                'highestRole' => $highestRole ? $highestRole->display_name : null,
            ],
        ]);
    }

    public function reorder(Request $request)
    {
        $roleIds = $request->input('roleIds', []);
        $adminRole = \App\Models\Role::where('name', 'admin')->first();

        if ($adminRole) {
            $adminIndex = array_search($adminRole->id, $roleIds);
            if ($adminIndex !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể thay đổi vị trí của quyền quản trị viên (admin)!'
                ], 400);
            }
        }

        foreach ($roleIds as $index => $roleId) {
            \App\Models\Role::where('id', $roleId)->update(['order' => $index]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Sắp xếp lại thứ tự role thành công'
        ]);
    }
} 