<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EmployeeResource;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    /**
     * Lấy danh sách nhân viên với filter và phân trang
     */
    public function employees(Request $request)
    {
        $employees = $this->userRepository->getEmployeesWithFilters($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách nhân viên thành công',
            'data' => [
                'employees' => EmployeeResource::collection($employees),
                'pagination' => [
                    'currentPage' => $employees->currentPage(),
                    'totalPages' => $employees->lastPage(),
                    'totalItems' => $employees->total(),
                    'itemsPerPage' => $employees->perPage(),
                ],
            ],
        ]);
    }

    /**
     * Lấy thống kê tổng quan về nhân viên
     */
    public function summary(Request $request)
    {
        $stats = $this->userRepository->getEmployeeStats();

        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê nhân viên thành công',
            'data' => [
                'totalEmployees' => $stats['totalEmployees'],
                'activeEmployees' => $stats['activeEmployees'],
                'inactiveEmployees' => $stats['inactiveEmployees'],
                'averageSalary' => $stats['averageSalary'] !== null ? round($stats['averageSalary']) : 0,
                'departmentStats' => $stats['departmentStats'],
                'roleStats' => $stats['roleStats'],
            ],
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $userData = $request->only('name', 'email', 'phone', 'position', 'departmentId', 'roleName');
        $userInfoData = $request->only('salary', 'address', 'birthDate', 'gender', 'education', 'experience', 'skills');
        $result = $this->userRepository->createEmployee($userData, $userInfoData);
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo nhân viên thành công',
                'data' => new EmployeeResource($result),
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tạo nhân viên thất bại.',
            ], 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        $userData = $request->only('name', 'email', 'phone', 'position', 'departmentId','roleName');
        $userInfoData = $request->only('salary', 'address', 'birthDate', 'gender', 'education', 'experience', 'skills');
        $result = $this->userRepository->updateEmployee($id, $userData, $userInfoData);

        if ($result) {

        return response()->json([
                'success' => true,
                'message' => 'Cập nhật nhân viên thành công',
                'data' => new EmployeeResource($result),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật nhân viên thất bại.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $result = $this->userRepository->deleteEmployee($id);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa nhân viên thành công',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Xóa nhân viên thất bại.',
            ], 500);
        }
    }

    public function details($id)
    {
        $user = $this->userRepository->details($id);
        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin nhân viên thành công',
            'data' => new EmployeeResource($user),
        ]);
    }

    public function manager(Request $request)
    {
        $truongphong = $this->userRepository->manager();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách trưởng phòng thành công',
            'data' => EmployeeResource::collection($truongphong),
        ]);
    }
} 