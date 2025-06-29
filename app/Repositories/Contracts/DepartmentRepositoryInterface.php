<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface DepartmentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Lấy danh sách phòng ban với filter và pagination
     */
    public function getDepartmentsWithFilters(array $filters, int $limit = 10): LengthAwarePaginator;

    /**
     * Lấy thống kê phòng ban
     */
    public function getDepartmentStats(): array;

    public function listDepartmentToSelect();
} 