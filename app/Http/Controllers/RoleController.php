<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Resources\RoleResource;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleSelectionResource;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::query();
        if ($request->has('status')) {
            $roles->where('status', $request->status);
        }
        if ($request->has('level')) {
            $roles->where('level', $request->level);
        }
        return RoleResource::collection($roles->paginate(20));
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return new RoleResource($role);
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create($request->validated());
        return new RoleResource($role);
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
        $role->delete();
        return response()->json(['message' => 'Xóa role thành công']);
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
} 