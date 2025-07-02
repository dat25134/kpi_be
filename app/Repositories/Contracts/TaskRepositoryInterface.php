<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface extends BaseRepositoryInterface
{
    public function getTasksWithFilters(array $filters, int $limit = 10): LengthAwarePaginator;
}