<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EmployeeResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Lấy danh sách nhân viên với filter và phân trang
     */
    public function employees(Request $request)
    {
        $limit = $request->input('limit', 10);
        
        $query = User::with(['info', 'department', 'projects'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $searchTerm = $request->input('search');
                $q->where(function ($subQ) use ($searchTerm) {
                    $subQ->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('email', 'like', "%{$searchTerm}%")
                         ->orWhere('phone', 'like', "%{$searchTerm}%");
                });
            })
            ->when($request->filled('department_id'), function ($q) use ($request) {
                $q->where('department_id', $request->input('department_id'));
            })
            ->when($request->filled('position'), function ($q) use ($request) {
                $q->where('position', $request->input('position'));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->input('status'));
            });

        $employees = $query->paginate($limit);

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
        $totalEmployees = User::count();
        $activeEmployees = User::where('status', 'active')->count();
        $inactiveEmployees = $totalEmployees - $activeEmployees;
        $averageSalary = UserInfo::join('users', 'user_info.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->avg('user_info.salary');

        $departmentStats = Department::withCount('employees')->get()->map(function ($department) {
            return [
                'departmentId' => $department->id,
                'departmentName' => $department->name,
                'departmentCode' => $department->code,
                'employeeCount' => $department->employees_count,
            ];
        });

        $positionStats = User::select('position', DB::raw('count(*) as count'))
            ->groupBy('position')
            ->get()
            ->map(function ($stat) {
                return [
                    'position' => $stat->position,
                    'count' => $stat->count,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê nhân viên thành công',
            'data' => [
                'totalEmployees' => $totalEmployees,
                'activeEmployees' => $activeEmployees,
                'inactiveEmployees' => $inactiveEmployees,
                'averageSalary' => $averageSalary !== null ? round($averageSalary) : 0,
                'departmentStats' => $departmentStats,
                'positionStats' => $positionStats,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'position' => 'required|string|in:employee,specialist,manager,director',
            'departmentId' => 'required|integer|exists:departments,id',
            'salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'birthDate' => 'nullable|date_format:d/m/Y',
            'gender' => 'nullable|string|in:male,female,other',
            'education' => 'nullable|string',
            'experience' => 'nullable|string',
            'skills' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        
        DB::beginTransaction();
        try {
            // 1. Tạo User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'position' => $validated['position'],
                'department_id' => $validated['departmentId'],
                'password' => Hash::make('password'), // Mật khẩu mặc định
                'employee_id' => 'EMP' . strtoupper(Str::random(6)), // Mã nhân viên tự động
                'status' => 'active',
                'join_date' => now(),
            ]);

            // 2. Tạo UserInfo
            UserInfo::create([
                'user_id' => $user->id,
                'salary' => $validated['salary'] ?? null,
                'address' => $validated['address'] ?? null,
                'birth_date' => isset($validated['birthDate']) ? Carbon::createFromFormat('d/m/Y', $validated['birthDate'])->format('Y-m-d') : null,
                'gender' => $validated['gender'] ?? null,
                'education' => $validated['education'] ?? null,
                'experience' => $validated['experience'] ?? null,
                'skills' => $validated['skills'] ?? [],
            ]);

            DB::commit();

            // Trả về resource với đầy đủ thông tin
            $newUser = User::with(['info', 'department', 'projects'])->find($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Tạo nhân viên thành công',
                'data' => new EmployeeResource($newUser),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Tạo nhân viên thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getPositionName($position)
    {
        return match ($position) {
            'employee'   => 'Nhân viên',
            'specialist' => 'Chuyên viên',
            'manager'    => 'Phó phòng',
            'director'   => 'Trưởng phòng',
            default      => $position,
        };
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $id,
            'position' => 'required|string|in:employee,specialist,manager,director',
            'departmentId' => 'required|integer|exists:departments,id',
            'salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'birthDate' => 'nullable|date_format:d/m/Y',
            'gender' => 'nullable|string|in:male,female,other',
            'education' => 'nullable|string',
            'experience' => 'nullable|string',
            'skills' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();   

        DB::beginTransaction();
        try {
            // Cập nhật User
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'position' => $validated['position'],
                'department_id' => $validated['departmentId'],
            ]);

            // Cập nhật UserInfo
            $user->info->update([
                'salary' => $validated['salary'] ?? null,
                'address' => $validated['address'] ?? null,
                'birth_date' => isset($validated['birthDate']) ? Carbon::createFromFormat('d/m/Y', $validated['birthDate'])->format('Y-m-d') : null,
                'gender' => $validated['gender'] ?? null,
                'education' => $validated['education'] ?? null,
                'experience' => $validated['experience'] ?? null,
                'skills' => $validated['skills'] ?? [],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật nhân viên thành công',
                'data' => new EmployeeResource($user),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật nhân viên thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa tài khoản quản trị viên.',
            ], 403);
        }

        DB::beginTransaction();
        try {
            $user->info()->delete();
            $user->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa nhân viên thành công',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Xóa nhân viên thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function details($id)
    {
        $user = User::with(['info', 'department', 'projects'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin nhân viên thành công',
            'data' => new EmployeeResource($user),
        ]);
    }
} 