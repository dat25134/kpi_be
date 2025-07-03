<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;
use App\Models\TaskProgress;

interface TaskProgressRepositoryInterface extends BaseRepositoryInterface
{
    public function createProgress(array $data): TaskProgress;
} 