<?php

namespace App\Repositories\Contracts;

interface BaseRepositoryInterface
{
    /**
     * Lấy tất cả records
     */
    public function all();

    /**
     * Lấy record theo ID
     */
    public function find($id);

    /**
     * Lấy record theo ID hoặc throw exception
     */
    public function findOrFail($id);

    /**
     * Tạo record mới
     */
    public function create(array $data);

    /**
     * Cập nhật record
     */
    public function update($id, array $data);

    /**
     * Xóa record
     */
    public function delete($id);

    /**
     * Lấy records với pagination
     */
    public function paginate($perPage = 15);

    /**
     * Tìm kiếm records
     */
    public function search(array $criteria);

    /**
     * Lấy record theo field và value
     */
    public function findBy($field, $value);
} 