<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Evaluation;

interface EvaluationRepositoryInterface
{
    /**
     * Lấy danh sách phiếu đánh giá với filter
     */
    public function getEvaluationsWithFilters(Request $request, bool $isAdmin): LengthAwarePaginator;

    /**
     * Lấy chi tiết phiếu đánh giá theo ID
     */
    public function findById(int $id): ?Evaluation;

    /**
     * Tạo phiếu đánh giá mới
     */
    public function create(array $data): Evaluation;

    /**
     * Cập nhật phiếu đánh giá
     */
    public function update(int $id, array $data): bool;

    /**
     * Xóa phiếu đánh giá
     */
    public function delete(int $id): bool;

    /**
     * Lấy phiếu đánh giá theo user và period
     */
    public function findByUserAndPeriod(int $userId, int $month, int $year): ?Evaluation;

    /**
     * Lưu đánh giá với các chi tiết đánh giá và work descriptions
     */
    public function saveEvaluation(Request $request, int $id, $user = null): bool;
} 