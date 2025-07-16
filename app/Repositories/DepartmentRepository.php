<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\User;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    public function __construct(Department $model)
    {
        parent::__construct($model);
    }

    public function getDepartmentsWithFilters(array $filters, int $limit = 10): LengthAwarePaginator
    {
        $query = $this->model->with(['manager', 'employees']);

        // Xử lý từng filter riêng biệt
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('code', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['manager_id']) && !empty($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }

        return $query->paginate($limit);
    }

    public function getDepartmentStats(): array
    {
        $total_departments = $this->model->count();
        $total_employees = User::count();
        $avg_employees_per_department = $total_departments > 0 ? (int) round($total_employees / $total_departments) : 0;
        $largest_department = $this->model->withCount('employees')
            ->orderByDesc('employees_count')
            ->first();

        return [
            'total_departments' => $total_departments,
            'total_employees' => $total_employees,
            'avg_employees_per_department' => $avg_employees_per_department,
            'largest_department' => $largest_department ? [
                'name' => $largest_department->name,
                'employee_count' => $largest_department->employees_count,
            ] : null,
        ];
    }

    /**
     * Lấy tất cả departments với relationships
     */
    public function getAllWithRelations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with(['manager', 'employees'])->get();
    }

    /**
     * Lấy department theo ID với relationships
     */
    public function findWithRelations($id)
    {
        return $this->model->with(['manager', 'employees'])->findOrFail($id);
    }

    public function listDepartmentToSelect()
    {
        return $this->model->get(['id', 'name']);
    }

    /**
     * Gán danh sách nhân viên vào phòng ban (không xử lý transaction ở đây)
     */
    public function assignEmployees($departmentId, array $employeeIds)
    {
        // Xóa department_id của các user thuộc phòng ban này nhưng không còn trong danh sách mới
        \App\Models\User::where('department_id', $departmentId)
            ->whereNotIn('id', $employeeIds)
            ->update(['department_id' => null]);
        // Gán department_id cho các user mới
        \App\Models\User::whereIn('id', $employeeIds)
            ->update(['department_id' => $departmentId]);
    }
}