<?php

namespace App\Repositories;

use App\Repositories\Contracts\TaskProgressRepositoryInterface;
use App\Models\TaskProgress;
use App\Models\Task;
use Illuminate\Support\Collection;

class TaskProgressRepository extends BaseRepository implements TaskProgressRepositoryInterface
{
    public function __construct(TaskProgress $model)
    {
        parent::__construct($model);
    }

    public function createProgress(array $data): TaskProgress
    {
        return TaskProgress::create($data);
    }
} 