<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Lấy danh sách nhân viên với filter và pagination
     */
    public function getEmployeesWithFilters(array $filters, int $limit = 10): LengthAwarePaginator;

    /**
     * Lấy thống kê nhân viên
     */
    public function getEmployeeStats(): array;

    /**
     * Tạo nhân viên mới với UserInfo
     */
    public function createEmployee(array $userData, array $userInfoData);

    /**
     * Cập nhật nhân viên với UserInfo
     */
    public function updateEmployee(int $id, array $userData, array $userInfoData);

    /**
     * Xóa nhân viên và UserInfo
     */
    public function deleteEmployee(int $id): bool;

    /**
     * Kiểm tra user có role admin không
     */
    public function isAdmin(int $id): bool;

    /**
     * Lấy user theo email
     */
    public function findByEmail(string $email);

    /**
     * Lấy user có role director
     */
    public function director();

    /**
     * Lấy thông tin nhân viên
     */
    public function details(int $id);
} 