<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Lấy tất cả records
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Lấy record theo ID
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Lấy record theo ID hoặc throw exception
     */
    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Tạo record mới
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Cập nhật record
     */
    public function update($id, array $data)
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record;
    }

    /**
     * Xóa record
     */
    public function delete($id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }

    /**
     * Lấy records với pagination
     */
    public function paginate($perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Tìm kiếm records
     */
    public function search(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get();
    }

    /**
     * Lấy model instance
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set model instance
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }
} 