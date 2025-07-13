<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    public function getTasksWithFilters(array $filters, int $limit = 10): LengthAwarePaginator
    {
        $currentUserId = Auth::user()->id;
        
        $query = Task::with(['category', 'department', 'assigner', 'mainAssignee', 'collaborators', 'progressHistory.user'])
            ->where(function ($q) use ($currentUserId) {
                $q->where('main_assignee_id', $currentUserId)
                  ->orWhere('assigner_id', $currentUserId)
                  ->orWhere('created_by', $currentUserId)
                  ->orWhereHas('collaborators', function ($subQuery) use ($currentUserId) {
                      $subQuery->where('user_id', $currentUserId);
                  });
            });

        if (isset($filters['startDate'])) {
            $query->whereDate('start_date', '>=', $filters['startDate']);
        }

        if (isset($filters['endDate'])) {
            $query->whereDate('start_date', '<=', $filters['endDate']);
        }

        if (isset($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }   

        if (isset($filters['departmentId'])) {
            $query->where('department_id', $filters['departmentId']);
        }

        if (isset($filters['status'])) {
            if ($filters['status'] == 'ongoing') {
                $query->where('status', '!=', 'completed');
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (isset($filters['search'])) {
            $query->where('content', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['itemsPerPage'])) {
            $limit = $filters['itemsPerPage'];
        }

        $query->orderBy('created_at', 'desc')->orderBy('content', 'asc');
        $tasks = $query->get();

        // Loại bỏ subtask mà user cũng có quyền xem task cha
        $userTaskIds = $tasks->pluck('id')->toArray();
        $visibleTasks = $tasks->filter(function($task) use ($userTaskIds) {
            return is_null($task->parent_id) || !in_array($task->parent_id, $userTaskIds);
        });

        // Phân trang thủ công
        $page = request('page', 1);
        $perPage = $limit;
        $paged = $visibleTasks->forPage($page, $perPage);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paged,
            $visibleTasks->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        return $paginator;
    }

    public function createTask(array $data)
    {
        $dataTaskCreate = [
            'content' => $data['content'],
            'start_date' =>  Carbon::parse($data['startDate'])->format('Y-m-d'),
            'due_date' => Carbon::parse($data['deadline'])->format('Y-m-d'),
            'category_id' => $data['category'],
            'department_id' => $data['department'] ?? null,
            'weight' => $data['count'],
            'assigner_id' => $data['assigner'],
            'main_assignee_id' => $data['mainHandler'],
            'status' => 'in_progress',
            'created_by' => Auth::user()->id,
            'parent_id' => $data['parent_id'] ?? null,
        ];
        $dataCollaborators = $data['assignees'];

        DB::beginTransaction();
        try {
            $task = $this->create($dataTaskCreate);
            $task->collaborators()->sync($dataCollaborators);
            // Xử lý upload file
            if (request()->hasFile('files')) {
                foreach (request()->file('files') as $file) {
                    $task->addMedia($file)->toMediaCollection('task_files');
                }
            }
            DB::commit();
            return $task;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function updateTask(int $id, array $data)
    {
        $task = $this->model->findOrFail($id);
        $dataUpdate = [
            'content' => $data['content'],
            'start_date' =>  Carbon::parse($data['startDate'])->format('Y-m-d'),
            'due_date' => Carbon::parse($data['deadline'])->format('Y-m-d'),
            'category_id' => $data['category'],
            'department_id' => $data['department'] ?? null,
            'weight' => $data['count'],
            'main_assignee_id' => $data['mainHandler'],
            'assigner_id' => $data['assigner'],
            'status' => $data['status'],
            'quality_weight' => $data['qualityWeight'] ?? null,
            'completed_at' => now(),
        ];
        $dataCollaborators = $data['assignees'];
        $updateReason = $data['changeReason'] ?? 'Cập nhật task';
        
        DB::beginTransaction();
        try {
            $task->update($dataUpdate);
            $task->collaborators()->sync($dataCollaborators);
            
            // Lưu lý do cập nhật vào task progress
            $task->progressHistory()->create([
                'user_id' => Auth::user()->id,
                'content' => "Cập nhật task - Lý do: {$updateReason}",
            ]);
            
            // Xử lý upload file (nếu có)
            if (request()->hasFile('files')) {
                foreach (request()->file('files') as $file) {
                    $task->addMedia($file)->toMediaCollection('task_files');
                }
            }
            DB::commit();
            return $task;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Lấy thống kê task của user hiện tại
     */
    public function getUserTaskStats(): array
    {
        $currentUserId = Auth::user()->id;
        
        // Tổng số task liên quan
        $totalTasks = Task::where(function ($q) use ($currentUserId) {
            $q->where('main_assignee_id', $currentUserId)
              ->orWhere('assigner_id', $currentUserId)
              ->orWhere('created_by', $currentUserId)
              ->orWhereHas('collaborators', function ($subQuery) use ($currentUserId) {
                  $subQuery->where('user_id', $currentUserId);
              });
        })->count();

        // Task theo trạng thái
        $statusStats = Task::where(function ($q) use ($currentUserId) {
            $q->where('main_assignee_id', $currentUserId)
              ->orWhere('assigner_id', $currentUserId)
              ->orWhere('created_by', $currentUserId)
              ->orWhereHas('collaborators', function ($subQuery) use ($currentUserId) {
                  $subQuery->where('user_id', $currentUserId);
              });
        })
        ->selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();

        // Task theo vai trò
        $roleStats = [
            'main_assignee' => Task::where('main_assignee_id', $currentUserId)->count(),
            'assigner' => Task::where('assigner_id', $currentUserId)->count(),
            'creator' => Task::where('created_by', $currentUserId)->count(),
            'collaborator' => Task::whereHas('collaborators', function ($q) use ($currentUserId) {
                $q->where('user_id', $currentUserId);
            })->count(),
        ];

        return [
            'total_tasks' => $totalTasks,
            'status_stats' => $statusStats,
            'role_stats' => $roleStats,
        ];
    }

    /**
     * Lấy danh sách phòng ban có trong tasks của user hiện tại
     */
    public function getUserTaskDepartments(): array
    {
        $currentUserId = Auth::user()->id;
        
        $departments = Task::where(function ($q) use ($currentUserId) {
            $q->where('main_assignee_id', $currentUserId)
              ->orWhere('assigner_id', $currentUserId)
              ->orWhere('created_by', $currentUserId)
              ->orWhereHas('collaborators', function ($subQuery) use ($currentUserId) {
                  $subQuery->where('user_id', $currentUserId);
              });
        })
        ->whereNotNull('department_id')
        ->with('department:id,name,code')
        ->get()
        ->pluck('department')
        ->unique('id')
        ->values()
        ->map(function ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
            ];
        })
        ->toArray();

        return $departments;
    }
}