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
        $query = Task::with(['category', 'assigner', 'mainAssignee', 'collaborators']);

        if (isset($filters['startDate'])) {
            $query->whereDate('start_date', '>=', $filters['startDate']);
        }

        if (isset($filters['endDate'])) {
            $query->whereDate('start_date', '<=', $filters['endDate']);
        }

        if (isset($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }   

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where('content', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['itemsPerPage'])) {
            $limit = $filters['itemsPerPage'];
        }

        $query->orderBy('created_at', 'desc')->orderBy('content', 'asc');
        return $query->paginate($limit);
    }

    public function createTask(array $data)
    {
        $dataTaskCreate = [
            'content' => $data['content'],
            'start_date' =>  Carbon::parse($data['startDate'])->format('Y-m-d'),
            'due_date' => Carbon::parse($data['deadline'])->format('Y-m-d'),
            'category_id' => $data['category'],
            'weight' => $data['count'],
            'assigner_id' => $data['assigner'],
            'main_assignee_id' => $data['mainHandler'],
            'status' => 'in_progress',
            'created_by' => Auth::user()->id,
        ];
        $dataCollaborators = $data['assignees'];

        DB::beginTransaction();
        try {
            $task = $this->create($dataTaskCreate);
            $task->collaborators()->sync($dataCollaborators);
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
            'weight' => $data['count'],
            'main_assignee_id' => $data['mainHandler'],
            'assigner_id' => $data['assigner'],
        ];
        $dataCollaborators = $data['assignees'];
        DB::beginTransaction();
        try {
            $task->update($dataUpdate);
            $task->collaborators()->sync($dataCollaborators);
            DB::commit();
            return $task;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}