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
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    protected $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function index(Request $request)
    {
        $roles = $this->roleRepository->getRole();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách vị trí thành công',
            'data' => RoleResource::collection($roles),
        ]);
    }

    public function show($id)
    {
        $role = $this->roleRepository->find($id);
        return response()->json([
            'status' => true,
            'message' => 'Lấy vị trí thành công',
            'data' => new RoleResource($role),
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleRepository->createRole($request->all());
        return response()->json([
            'status' => true,
            'message' => 'Tạo vị trí mới thành công',
            'data' => new RoleResource($role),
        ]);
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        $result = $this->roleRepository->updateRole($id, $request->all());
        if ($result) {  
            return response()->json([
                'status' => true,
                'message' => 'Cập nhật vị trí thành công',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Cập nhật vị trí thất bại',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $role = $this->roleRepository->find($id);
        if ($role->name == 'admin') {   
            return response()->json(['message' => 'Không thể xóa vị trí admin'], 500);
        }
        $role->delete();
        return response()->json([
            'status' => true,
            'message' => 'Xóa vị trí thành công',
        ]);
    }

    public function selection()
    {
        $roles = $this->roleRepository->listRoleToSelect();
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách role thành công',
            'data' => RoleSelectionResource::collection($roles),
        ]);
    }

    public function summary()
    {
        $summary = $this->roleRepository->summary();

        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê vị trí thành công',
            'data' => [
                'totalRoles' => $summary['totalRoles'],
                'activeRoles' => $summary['activeRoles'],
                'inactiveRoles' => $summary['inactiveRoles'],
                'totalEmployees' => $summary['totalEmployees'],
                'highestRole' => $summary['highestRole'] ? $summary['highestRole']->display_name : null,
            ],
        ]);
    }

    public function reorder(Request $request)
    {  
        try{ 
        $roleIds = $request->input('roleIds', []);
        $adminRole = $this->roleRepository->findBy('name', 'admin');

        if ($adminRole) {
            $adminIndex = array_search($adminRole->id, $roleIds);
            if ($adminIndex !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể thay đổi vị trí của quyền quản trị viên (admin)!'
                ], 400);
            }
        }
        DB::beginTransaction();
        foreach ($roleIds as $index => $roleId) {
            \App\Models\Role::where('id', $roleId)->update(['order' => $index]);
        }
        DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'Sắp xếp lại thứ tự role thành công'
        ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Sắp xếp lại thứ tự role thất bại'
            ], 500);
        }
    }
} 