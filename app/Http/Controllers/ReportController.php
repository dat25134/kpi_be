<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

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

    /**
     * Xu hướng KPI theo tháng trong năm
     */
    public function kpiTrends(Request $request)
    {
        $year = $request->query('year', now()->year);
        $departmentId = $request->query('departmentId') == 'all' ? null : $request->query('departmentId');

        // Validate year
        if (!is_numeric($year) || $year < 2000 || $year > 2100) {
            return response()->json([
                'message' => 'Tham số year không hợp lệ.'
            ], 422);
        }
        // Validate departmentId nếu có
        if ($departmentId !== null && (!is_numeric($departmentId) || !\App\Models\Department::where('id', $departmentId)->exists())) {
            return response()->json([
                'message' => 'Tham số departmentId không hợp lệ.'
            ], 422);
        }

        // Lấy target mặc định (ví dụ: 80, có thể lấy từ config hoặc DB nếu có)
        $defaultTarget = 100;
        // Nếu có bảng lưu target KPI theo tháng/phòng ban, có thể lấy động ở đây
        $targets = array_fill(1, 12, $defaultTarget); // [1 => 80, 2 => 80, ...]

        // Nếu có bảng lưu target KPI riêng từng tháng, từng phòng ban, có thể join ở đây
        // ...

        // Lấy achieved (trung bình cộng total_score) theo từng tháng
        $achievedByMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $evaQuery = \App\Models\Evaluation::where('month', $m)->where('year', $year);
            if ($departmentId) {
                $evaQuery->where('department', function($q) use ($departmentId) {
                    $q->select('name')->from('departments')->where('id', $departmentId)->limit(1);
                });
            }
            $achievedByMonth[$m] = round($evaQuery->avg('total_score') ?? 0, 2);
        }

        // Chuẩn bị dữ liệu trả về đủ 12 tháng
        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStr = 'T' . $m;
            $result[] = [
                'month' => $monthStr,
                'target' => $targets[$m] ?? 0,
                'achieved' => $achievedByMonth[$m] ?? 0
            ];
        }
        return response()->json($result);
    }

    /**
     * Top Performers (top 5 nhân viên có KPI/score cao nhất)
     */
    public function topPerformers(Request $request)
    {
        $departmentId = $request->query('departmentId') == 'all' ? null : $request->query('departmentId');

        // Validate departmentId nếu có
        if ($departmentId !== null && (!is_numeric($departmentId) || !\App\Models\Department::where('id', $departmentId)->exists())) {
            return response()->json([
                'message' => 'Tham số departmentId không hợp lệ.'
            ], 422);
        }

        // Lấy evaluation mới nhất của từng user (theo tháng/năm lớn nhất)
        $evaluationQuery = \App\Models\Evaluation::query();
        if ($departmentId) {
            $departmentName = \App\Models\Department::find($departmentId)->name;
            $evaluationQuery->where('department', $departmentName);
        }
        $evaluationQuery->whereNotNull('total_score');
        $evaluations = $evaluationQuery->orderByDesc('year')->orderByDesc('month')->get();

        $latestEvaluations = $evaluations
            ->groupBy('user_id')
            ->map(function($group) {
                return $group->first();
            })
            ->sortByDesc('total_score')
            ->take(5);

        $result = [];
        foreach ($latestEvaluations as $eva) {
            $user = \App\Models\User::find($eva->user_id);
            $result[] = [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar ?? null,
                'position' => optional($user->roles->first())->display_name ?? optional($user->roles->first())->name ?? null,
                'kpi' => round($eva->total_score, 2),
                'score' => round($eva->total_score, 2)
            ];
        }
        return response()->json($result);
    }

    /**
     * Alerts & Notifications cho dashboard/report
     */
    public function alertsNotifications(Request $request)
    {
        $departmentId = $request->query('departmentId') == 'all' ? null : $request->query('departmentId');
        $now = now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();

        // Validate departmentId nếu có
        if ($departmentId !== null && (!is_numeric($departmentId) || !\App\Models\Department::where('id', $departmentId)->exists())) {
            return response()->json([
                'message' => 'Tham số departmentId không hợp lệ.'
            ], 422);
        }

        // Overdue tasks
        $overdueTasksQuery = \App\Models\Task::whereIn('status', ['pending', 'in_progress'])
            ->where('due_date', '<', $now);
        // Upcoming tasks (đến hạn trong tuần này)
        $upcomingTasksQuery = \App\Models\Task::whereIn('status', ['pending', 'in_progress'])
            ->whereBetween('due_date', [$now, $endOfWeek]);
        if ($departmentId) {
            $overdueTasksQuery->where('department_id', $departmentId);
            $upcomingTasksQuery->where('department_id', $departmentId);
        }
        $overdueTasks = $overdueTasksQuery->count();
        $upcomingTasks = $upcomingTasksQuery->count();

        // New employees chưa được phân công việc (giả sử chưa có task nào qua các quan hệ)
        $newEmployeesQuery = \App\Models\User::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year);
        if ($departmentId) {
            $newEmployeesQuery->where('department_id', $departmentId);
        }
        $newEmployees = $newEmployeesQuery
            ->whereDoesntHave('assignedTasks')
            ->whereDoesntHave('assignedByTasks')
            ->whereDoesntHave('collaboratedTasks')
            ->count();

        // Achieved targets: phòng ban đạt/vượt mục tiêu tháng (giả sử target mặc định là 100, lấy trung bình total_score của evaluations trong tháng)
        $achievedTargets = [];
        $departments = $departmentId
            ? \App\Models\Department::where('id', $departmentId)->get()
            : \App\Models\Department::all();
        foreach ($departments as $dept) {
            $avgScore = \App\Models\Evaluation::where('department', $dept->name)
                ->where('month', $now->month)
                ->where('year', $now->year)
                ->avg('total_score');
            $target = 100; // target mặc định
            if ($avgScore !== null && $target > 0 && $avgScore >= $target) {
                $percent = round($avgScore / $target * 100);
                $achievedTargets[] = [
                    'department' => $dept->name,
                    'percent' => $percent
                ];
            }
        }

        // Overdue task details
        $overdueTaskDetails = $overdueTasksQuery->get()->map(function($task) use ($now) {
            $department = optional($task->department)->name ?? null;
            $assignee = optional($task->mainAssignee)->name ?? null;
            $overdueDays = $now->diffInDays(optional($task->due_date) ? \Carbon\Carbon::parse($task->due_date) : $now, false);
            $overdueDays = abs($overdueDays);
            return [
                'id' => $task->id,
                'name' => $task->content,
                'department' => $department,
                'overdueDays' => round($overdueDays),
                'assignee' => $assignee
            ];
        })->values();

        return response()->json([
            'overdueTasks' => $overdueTasks,
            'upcomingTasks' => $upcomingTasks,
            'newEmployees' => $newEmployees,
            'achievedTargets' => $achievedTargets,
            'overdueTaskDetails' => $overdueTaskDetails
        ]);
    }

    /**
     * Phân bố theo phòng ban (Pie Chart)
     */
    public function departmentDistribution(Request $request)
    {
        $departmentId = $request->query('departmentId') == 'all' ? null : $request->query('departmentId');

        // Validate departmentId nếu có
        if ($departmentId !== null && (!is_numeric($departmentId) || !\App\Models\Department::where('id', $departmentId)->exists())) {
            return response()->json([
                'message' => 'Tham số departmentId không hợp lệ.'
            ], 422);
        }

        if ($departmentId) {
            // Lấy đúng 1 phòng ban theo id
            $departments = \App\Models\Department::where('id', $departmentId)->get();
        } else {
            // Lấy tất cả phòng ban
            $departments = \App\Models\Department::all();
        }

        $result = $departments->map(function($dept) {
            $employees = \App\Models\User::where('department_id', $dept->id)->count();
            return [
                'id' => $dept->id,
                'name' => $dept->code,
                'employees' => $employees
            ];
        })->values();

        return response()->json($result);
    }

    /**
     * Hiệu suất tháng (Monthly Performance)
     */
    public function monthlyPerformance(Request $request)
    {
        $departmentId = $request->query('departmentId') == 'all' ? null : $request->query('departmentId');
        $time = $request->query('time', 'month');
        $now = now();

        // Validate time
        $validTimes = ['week', 'month', 'quarter', 'year'];
        if (!in_array($time, $validTimes)) {
            return response()->json(['message' => 'Tham số time không hợp lệ.'], 422);
        }

        // Validate departmentId nếu có
        if ($departmentId !== null && (!is_numeric($departmentId) || !\App\Models\Department::where('id', $departmentId)->exists())) {
            return response()->json(['message' => 'Tham số departmentId không hợp lệ.'], 422);
        }

        // Xác định khoảng thời gian
        if ($time === 'week') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
        } elseif ($time === 'month') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        } elseif ($time === 'quarter') {
            $start = $now->copy()->startOfQuarter();
            $end = $now->copy()->endOfQuarter();
        } else { // year
            $start = $now->copy()->startOfYear();
            $end = $now->copy()->endOfYear();
        }

        // Query task theo khoảng thời gian và phòng ban nếu có
        $taskQuery = \App\Models\Task::whereBetween('created_at', [$start, $end]);
        if ($departmentId) {
            $taskQuery->where('department_id', $departmentId);
        }
        $tasks = $taskQuery->get();
        $totalTasks = $tasks->count();

        // Tính onTimeRate: % task completed đúng hạn
        $onTime = $tasks->where('status', 'completed')->filter(function($task) {
            return $task->completed_at && $task->due_date && $task->completed_at <= $task->due_date;
        })->count();
        $onTimeRate = $totalTasks > 0 ? round($onTime / $totalTasks * 100) : 0;

        // Tính averageQuality: trung bình quality_weight (thang 1-5) quy đổi về %
        $averageQuality = $totalTasks > 0 ? round($tasks->avg('quality_weight') / 5 * 100) : 0;

        // Tính averageComplexity: trung bình weight (thang 1-5) quy đổi về %
        $averageComplexity = $totalTasks > 0 ? round($tasks->avg('weight') / 5 * 100) : 0;

        return response()->json([
            'onTimeRate' => $onTimeRate,
            'averageQuality' => $averageQuality,
            'averageComplexity' => $averageComplexity
        ]);
    }

    /**
     * Hoạt động gần đây (Recent Activities)
     */
    public function recentActivities(Request $request)
    {
        $departmentId = $request->query('departmentId') == 'all' ? null : $request->query('departmentId');
        $time = $request->query('timeFilter', 'month');
        $now = now();

        // Validate time
        $validTimes = ['week', 'month', 'quarter', 'year'];
        if (!in_array($time, $validTimes)) {
            return response()->json(['message' => 'Tham số timeFilter không hợp lệ.'], 422);
        }
        // Validate departmentId nếu có
        if ($departmentId !== null && (!is_numeric($departmentId) || !\App\Models\Department::where('id', $departmentId)->exists())) {
            return response()->json(['message' => 'Tham số departmentId không hợp lệ.'], 422);
        }

        // Xác định khoảng thời gian
        if ($time === 'week') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
        } elseif ($time === 'month') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        } elseif ($time === 'quarter') {
            $start = $now->copy()->startOfQuarter();
            $end = $now->copy()->endOfQuarter();
        } else { // year
            $start = $now->copy()->startOfYear();
            $end = $now->copy()->endOfYear();
        }

        $activityQuery = Activity::whereBetween('created_at', [$start, $end]);
        if ($departmentId) {
            $activityQuery->whereHasMorph(
                'causer',
                [\App\Models\User::class],
                function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                }
            );
        }
        $activities = $activityQuery->orderByDesc('created_at')->limit(5)->get();

        $typeColor = [
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'default' => 'purple',
        ];

        $result = $activities->map(function($act) use ($typeColor) {
            // Lấy tên người thực hiện (causer)
            if ($act->causer_type === \App\Models\User::class && $act->causer_id) {
                $user = \App\Models\User::find($act->causer_id);
                $causerName = $user ? $user->name : 'Hệ thống';
            } else {
                $causerName = 'Hệ thống';
            }

            // Lấy tên đối tượng (subject)
            $subjectName = null;
            if ($act->subject_type && $act->subject_id) {
                $subjectModel = app($act->subject_type);
                $subject = $subjectModel->find($act->subject_id);
                $subjectName = $subject->name ?? $subject->content ?? $subject->title ?? null;
            }

            // Lấy mô tả hành động
            $action = $act->description;

            // Build content
            if ($causerName && $subjectName) {
                $content = "{$causerName} đã {$action} \"{$subjectName}\"";
            } elseif ($causerName) {
                $content = "{$causerName} đã {$action}";
            } else {
                $content = $action;
            }

            $type = $act->event ?? 'default';
            return [
                'id' => $act->id,
                'type' => $type,
                'content' => $content,
                'color' => $typeColor[$type] ?? $typeColor['default']
            ];
        })->values();

        return response()->json($result);
    }
} 