<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

        return $query->paginate($limit);
    }
}