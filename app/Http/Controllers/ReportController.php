<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\Task;

class ReportController extends Controller
{
    /**
     * Tổng quan dashboard
     */
    public function overview(Request $request)
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;
        $time = $request->query('time', 'month');
        $departmentId = $request->query('department');

        // Validate time
        $validTimes = ['week', 'month', 'quarter', 'year'];
        if (!in_array($time, $validTimes)) {
            return response()->json([
                'message' => 'Tham số time không hợp lệ. Chỉ chấp nhận: week, month, quarter, year.'
            ], 422);
        }
        // Validate department
        if ($departmentId !== null) {
            if (!is_numeric($departmentId) || !\App\Models\Department::where('id', $departmentId)->exists()) {
                return response()->json([
                    'message' => 'Tham số department không hợp lệ.'
                ], 422);
            }
        }

        // Query user theo phòng ban nếu có
        $userQuery = \App\Models\User::query();
        if ($departmentId) {
            $userQuery->where('department_id', $departmentId);
        }

        $totalEmployees = $userQuery->count();
        $activeEmployees = (clone $userQuery)->where('status', 'active')->count();
        $inactiveEmployees = (clone $userQuery)->where('status', '!=', 'active')->count();

        // Nhân viên mới theo filter thời gian
        $newEmployeesQuery = (clone $userQuery);
        if ($time === 'week') {
            $newEmployeesQuery->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        } elseif ($time === 'month') {
            $newEmployeesQuery->whereMonth('created_at', $month)->whereYear('created_at', $year);
        } elseif ($time === 'quarter') {
            $newEmployeesQuery->whereBetween('created_at', [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()]);
        } elseif ($time === 'year') {
            $newEmployeesQuery->whereYear('created_at', $year);
        }
        $newEmployeesThisPeriod = $newEmployeesQuery->count();

        // Các thống kê khác (có thể filter task theo phòng ban nếu cần)
        $totalDepartments = \App\Models\Department::count();
        $totalPositions = \App\Models\Role::count();
        $activePositions = \App\Models\Role::where('status', 'active')->count();
        $totalTasks = \App\Models\Task::count();
        $completedTasks = \App\Models\Task::where('status', 'completed')->count();
        $ongoingTasks = \App\Models\Task::whereIn('status', ['pending', 'in_progress'])->count();
        $overdueTasks = \App\Models\Task::whereIn('status', ['pending', 'in_progress'])->where('due_date', '<', $now)->count();

        return response()->json([
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'inactiveEmployees' => $inactiveEmployees,
            'newEmployeesThisMonth' => $newEmployeesThisPeriod,
            'totalDepartments' => $totalDepartments,
            'totalPositions' => $totalPositions,
            'activePositions' => $activePositions,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'ongoingTasks' => $ongoingTasks,
            'overdueTasks' => $overdueTasks,
        ]);
    }

    /**
     * Thống kê theo phòng ban
     */
    public function departmentStats(Request $request)
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;
        $time = $request->query('time', 'month');

        // Validate time
        $validTimes = ['week', 'month', 'quarter', 'year'];
        if (!in_array($time, $validTimes)) {
            return response()->json([
                'message' => 'Tham số time không hợp lệ. Chỉ chấp nhận: week, month, quarter, year.'
            ], 422);
        }

        $departments = \App\Models\Department::all();
        $result = [];
        foreach ($departments as $dept) {
            // Nhân viên thuộc phòng ban
            $employeesQuery = \App\Models\User::where('department_id', $dept->id);
            $employees = $employeesQuery->count();
            // $avgSalary = $employeesQuery->avg('salary') ?? 0;

            // Task thuộc phòng ban (giả sử task có department_id)
            $taskQuery = \App\Models\Task::where('department_id', $dept->id);
            if ($time === 'week') {
                $taskQuery->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            } elseif ($time === 'month') {
                $taskQuery->whereMonth('created_at', $month)->whereYear('created_at', $year);
            } elseif ($time === 'quarter') {
                $taskQuery->whereBetween('created_at', [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()]);
            } elseif ($time === 'year') {
                $taskQuery->whereYear('created_at', $year);
            }
            $completed = (clone $taskQuery)->where('status', 'completed')->count();
            $ongoing = (clone $taskQuery)->whereIn('status', ['pending', 'in_progress'])->count();
            $overdue = (clone $taskQuery)->whereIn('status', ['pending', 'in_progress'])->where('due_date', '<', $now)->count();

            $result[] = [
                'id' => $dept->id,
                'name' => $dept->name,
                'code' => $dept->code,
                'employees' => $employees,
                'completed' => $completed,
                'ongoing' => $ongoing,
                'overdue' => $overdue
                // 'avgSalary' => round($avgSalary)
            ];
        }
        return response()->json($result);
    }

    /**
     * Thống kê theo chức vụ
     */
    public function positionStats(Request $request)
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;
        $time = $request->query('time', 'month');

        // Validate time
        $validTimes = ['week', 'month', 'quarter', 'year'];
        if (!in_array($time, $validTimes)) {
            return response()->json([
                'message' => 'Tham số time không hợp lệ. Chỉ chấp nhận: week, month, quarter, year.'
            ], 422);
        }

        $roles = \App\Models\Role::all();
        $result = [];
        foreach ($roles as $role) {
            // Lấy user theo role
            $userQuery = \App\Models\User::whereHas('roles', function($q) use ($role) {
                $q->where('id', $role->id);
            });
            if ($time === 'week') {
                $userQuery->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            } elseif ($time === 'month') {
                $userQuery->whereMonth('created_at', $month)->whereYear('created_at', $year);
            } elseif ($time === 'quarter') {
                $userQuery->whereBetween('created_at', [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()]);
            } elseif ($time === 'year') {
                $userQuery->whereYear('created_at', $year);
            }
            $count = $userQuery->count();
            $result[] = [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'count' => $count
            ];
        }
        return response()->json($result);
    }

    /**
     * Tiến độ công việc theo tháng (3 tháng gần nhất)
     */
    public function taskProgress(Request $request)
    {
        $now = now();
        $time = $request->query('time', 'month');
        $departmentId = $request->query('department', 'all');

        // Validate time
        $validTimes = ['week', 'month', 'quarter', 'year'];
        if (!in_array($time, $validTimes)) {
            return response()->json([
                'message' => 'Tham số time không hợp lệ. Chỉ chấp nhận: week, month, quarter, year.'
            ], 422);
        }
        // Validate department
        if ($departmentId !== 'all' && (!is_numeric($departmentId) || !\App\Models\Department::where('id', $departmentId)->exists())) {
            return response()->json([
                'message' => 'Tham số department không hợp lệ.'
            ], 422);
        }

        $result = [];
        // Lấy 3 mốc thời gian gần nhất theo filter
        if ($time === 'month') {
            for ($i = 2; $i >= 0; $i--) {
                $date = $now->copy()->subMonths($i);
                $monthLabel = 'T' . $date->month;
                $taskQuery = \App\Models\Task::query();
                if ($departmentId !== 'all') {
                    $taskQuery->where('department_id', $departmentId);
                }
                $taskQuery->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year);
                $completed = (clone $taskQuery)->where('status', 'completed')->count();
                $ongoing = (clone $taskQuery)->whereIn('status', ['pending', 'in_progress'])->count();
                $overdue = (clone $taskQuery)->whereIn('status', ['pending', 'in_progress'])->where('due_date', '<', $now)->count();
                $result[] = [
                    'month' => $monthLabel,
                    'completed' => $completed,
                    'ongoing' => $ongoing,
                    'overdue' => $overdue
                ];
            }
        } else {
            // Các filter khác: chỉ trả về 1 block
            $taskQuery = \App\Models\Task::query();
            if ($departmentId !== 'all') {
                $taskQuery->where('department_id', $departmentId);
            }
            if ($time === 'week') {
                $label = 'Tuần ' . $now->weekOfYear;
                $taskQuery->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            } elseif ($time === 'quarter') {
                $label = 'Q' . $now->quarter;
                $taskQuery->whereBetween('created_at', [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()]);
            } elseif ($time === 'year') {
                $label = 'Năm ' . $now->year;
                $taskQuery->whereYear('created_at', $now->year);
            }
            $completed = (clone $taskQuery)->where('status', 'completed')->count();
            $ongoing = (clone $taskQuery)->whereIn('status', ['pending', 'in_progress'])->count();
            $overdue = (clone $taskQuery)->whereIn('status', ['pending', 'in_progress'])->where('due_date', '<', $now)->count();
            $result[] = [
                'month' => $label,
                'completed' => $completed,
                'ongoing' => $ongoing,
                'overdue' => $overdue
            ];
        }
        return response()->json($result);
    }
} 