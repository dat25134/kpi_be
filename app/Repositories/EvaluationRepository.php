<?php
namespace App\Repositories;

use App\Models\Evaluation;
use App\Repositories\Interfaces\EvaluationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class EvaluationRepository implements EvaluationRepositoryInterface
{
    protected $model;

    public function __construct(Evaluation $evaluation)
    {
        $this->model = $evaluation;
    }

    /**
     * Lấy danh sách phiếu đánh giá với filter
     */
    public function getEvaluationsWithFilters(Request $request, bool $isAdmin): LengthAwarePaginator
    {
        $roleType = $request->input('type') ?? null;
        if (! $roleType) {
            throw new \InvalidArgumentException('Tham số type không hợp lệ');
        }

        $query = $this->model->query()->with(['user.roles', 'user.department']);

        if ($roleType !== 'personal') {
            $query->whereHas('user.roles', function ($q) use ($roleType) {
                $q->where('name', $roleType);
            });
        } else {
            $query->where('user_id', Auth::id());
        }

        // Lọc theo tên
        if ($request->filled('name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('name') . '%');
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Lọc theo xếp loại
        if ($request->filled('final_grade')) {
            $query->where('final_grade', $request->input('final_grade'));
        }

        // Lọc theo period (tháng/năm)
        if ($request->filled('month') && $request->filled('year')) {
            $month = $request->input('month');
            $year  = $request->input('year');
            $query->where('month', $month)->where('year', $year);
        }

        // Phân trang
        $page     = (int) ($request->input('page', 1));
        $pageSize = (int) ($request->input('pageSize', 10));

        return $query->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate($pageSize, ['*'], 'page', $page);
    }

    /**
     * Lấy chi tiết phiếu đánh giá theo ID
     */
    public function findById(int $id): ?Evaluation
    {
        return $this->model->with([
            'user.roles',
            'user.department',
            'evaluationDetails.criteria',
            'workDescriptions',
        ])->find($id);
    }

    /**
     * Tạo phiếu đánh giá mới
     */
    public function create(array $data): Evaluation
    {
        return $this->model->create($data);
    }

    /**
     * Cập nhật phiếu đánh giá
     */
    public function update(int $id, array $data): bool
    {
        $evaluation = $this->model->find($id);
        if (! $evaluation) {
            return false;
        }
        return $evaluation->update($data);
    }

    /**
     * Xóa phiếu đánh giá
     */
    public function delete(int $id): bool
    {
        $evaluation = $this->model->find($id);
        if (! $evaluation) {
            return false;
        }
        return $evaluation->delete();
    }

    /**
     * Lấy phiếu đánh giá theo user và period
     */
    public function findByUserAndPeriod(int $userId, int $month, int $year): ?Evaluation
    {
        return $this->model->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }
}
