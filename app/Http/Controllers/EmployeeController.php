<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EmployeeResource;

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
        $averageSalary = UserInfo::avg('salary');

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
                    'position' => $this->getPositionName($stat->position),
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
                'averageSalary' => (float) $averageSalary,
                'departmentStats' => $departmentStats,
                'positionStats' => $positionStats,
            ],
        ]);
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
} 