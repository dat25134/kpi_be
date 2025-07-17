<?php
namespace App\Repositories;

use App\Models\Evaluation;
use App\Repositories\Interfaces\EvaluationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    /**
     * Lưu đánh giá với các chi tiết đánh giá và work descriptions
     */
    public function saveEvaluation(Request $request, int $id, $user = null): bool
    {
        $evaluation = $this->model->find($id);
        if (!$evaluation) {
            return false;
        }

        try {
            DB::beginTransaction();

            $newStatus = $request->input('status', $evaluation->status);
            
            // Kiểm tra quyền thay đổi status theo quy trình
            if (!$this->canChangeStatus($evaluation->status, $newStatus)) {
                throw new \InvalidArgumentException('Không thể chuyển từ status ' . $evaluation->status . ' sang ' . $newStatus);
            }

            // Cập nhật thông tin cơ bản của evaluation
            $evaluationData = [
                'status' => $newStatus,
            ];

            $evaluation->update($evaluationData);

            // Lưu evaluation details theo giai đoạn hiện tại
            if ($request->has('evaluation_details')) {
                $this->saveEvaluationDetailsByStage($evaluation->id, $request->input('evaluation_details'), $newStatus);
            }

            // Tính lại total_score và final_grade
            $this->recalculateTotalScoreAndFinalGrade($evaluation);

            // Cập nhật work descriptions (chỉ cấp 1 mới có quyền)
            // TODO: Tạm thời comment để tập trung vào chức năng đánh giá theo từng cấp
            if ($request->has('work_descriptions')) {
                if (!$this->canUpdateWorkDescriptions($user, $evaluation)) {
                    throw new \InvalidArgumentException('Bạn không có quyền cập nhật work descriptions');
                }
                $this->updateWorkDescriptions($evaluation->id, $request->input('work_descriptions'));
            }


            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Kiểm tra quyền thay đổi status theo quy trình đánh giá
     */
    private function canChangeStatus(string $currentStatus, string $newStatus): bool
    {
        $allowedTransitions = [
            'draft' => ['draft', 'submitted'],
            'submitted' => ['submitted', 'level1_approved'],
            'level1_approved' => ['level1_approved', 'level2_approved'],
            'level2_approved' => ['level2_approved', 'completed'],
            'completed' => ['completed'], // Không thể thay đổi sau khi hoàn thành
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    /**
     * Lưu chi tiết đánh giá theo giai đoạn
     */
    private function saveEvaluationDetailsByStage(int $evaluationId, array $details, string $status): void
    {
        foreach ($details as $detail) {
            $criteriaId = $detail['criteria_id'];
            
            $detailData = [
                'evaluation_id' => $evaluationId,
                'criteria_id' => $criteriaId,
            ];

            // Chỉ lưu dữ liệu theo giai đoạn hiện tại
            switch ($status) {
                case 'draft':
                case 'submitted':
                    // Giai đoạn tự đánh giá - chỉ lưu self_score và self_comment
                    if (isset($detail['self_score'])) {
                        $detailData['self_score'] = $detail['self_score'];
                    }
                    if (isset($detail['self_comment'])) {
                        $detailData['self_comment'] = $detail['self_comment'];
                    }
                    // Tính final_score dựa trên self_score
                    if (isset($detail['self_score'])) {
                        $detailData['final_score'] = $detail['self_score'];
                    }
                    break;

                case 'level1_approved':
                    // Giai đoạn đánh giá cấp 1 - chỉ lưu level1_score và level1_comment
                    if (isset($detail['level1_score'])) {
                        $detailData['level1_score'] = $detail['level1_score'];
                    }
                    if (isset($detail['level1_comment'])) {
                        $detailData['level1_comment'] = $detail['level1_comment'];
                    }
                    // Tính final_score dựa trên level1_score
                    if (isset($detail['level1_score'])) {
                        $detailData['final_score'] = $detail['level1_score'];
                    }
                    break;

                case 'level2_approved':
                    // Giai đoạn đánh giá cấp 2 - chỉ lưu level2_score và level2_comment
                    if (isset($detail['level2_score'])) {
                        $detailData['level2_score'] = $detail['level2_score'];
                    }
                    if (isset($detail['level2_comment'])) {
                        $detailData['level2_comment'] = $detail['level2_comment'];
                    }
                    // Tính final_score dựa trên level2_score
                    if (isset($detail['level2_score'])) {
                        $detailData['final_score'] = $detail['level2_score'];
                    }
                    break;

                default:
                    // Các giai đoạn khác không cho phép thay đổi evaluation details
                    continue 2;
            }

            // Cập nhật hoặc tạo mới evaluation detail
            \App\Models\EvaluationDetail::updateOrCreate(
                ['evaluation_id' => $evaluationId, 'criteria_id' => $criteriaId],
                $detailData
            );
        }
    }

    /**
     * Tính lại total_score và final_grade cho evaluation
     */
    private function recalculateTotalScoreAndFinalGrade($evaluation)
    {
        // Lấy tổng final_score của tất cả evaluation details
        $totalScore = $evaluation->evaluationDetails()->sum('final_score');
        $evaluation->total_score = $totalScore;

        // Tính final_grade
        if ($totalScore >= 90) {
            $grade = 'A';
        } elseif ($totalScore >= 70) {
            $grade = 'B';
        } elseif ($totalScore >= 50) {
            $grade = 'C';
        } else {
            $grade = 'D';
        }
        $evaluation->final_grade = $grade;
        $evaluation->save();
    }

    /**
     * Lưu mô tả công việc
     */
    private function saveWorkDescriptions(int $evaluationId, array $workDescriptions): void
    {
        // Xóa các work descriptions cũ
        \App\Models\WorkDescription::where('evaluation_id', $evaluationId)->delete();

        foreach ($workDescriptions as $index => $workDesc) {
            $workDescData = [
                'evaluation_id' => $evaluationId,
                'task_id' => $workDesc['task_id'] ?? null,
                'task_title' => $workDesc['task_title'] ?? null,
                'task_description' => $workDesc['task_description'] ?? null,
                'task_status' => $workDesc['task_status'] ?? null,
                'task_start_date' => $workDesc['task_start_date'] ?? null,
                'task_due_date' => $workDesc['task_due_date'] ?? null,
                'task_weight' => $workDesc['task_weight'] ?? null,
                'unit' => $workDesc['unit'] ?? null,
                'target' => $workDesc['target'],
                'quality_weight' => $workDesc['quality_weight'],
                'result_level' => $workDesc['result_level'],
                'result_score' => $workDesc['result_score'] ?? null,
                'final_score' => $workDesc['final_score'] ?? null,
                'explanation' => $workDesc['explanation'] ?? null,
                'order' => $index + 1,
            ];

            \App\Models\WorkDescription::create($workDescData);
        }
    }

    /**
     * Cập nhật mô tả công việc (chỉ result_level và quality_weight)
     */
    
    public function updateWorkDescriptions(int $evaluationId, array $workDescriptions): void
    {
        foreach ($workDescriptions as $workDesc) {
            $updateData = [];
            
            // Chỉ cập nhật nếu có dữ liệu
            if (isset($workDesc['result_level'])) {
                $updateData['result_level'] = $workDesc['result_level'];
            }
            if (isset($workDesc['quality_weight'])) {
                $updateData['quality_weight'] = $workDesc['quality_weight'];
            }
            
            // Chỉ cập nhật nếu có dữ liệu để cập nhật
            if (!empty($updateData)) {
                \App\Models\WorkDescription::where('evaluation_id', $evaluationId)
                    ->where('id', $workDesc['id'])
                    ->update($updateData);
            }
        }
    }


    /**
     * Kiểm tra quyền cập nhật work descriptions
     */

    public function canUpdateWorkDescriptions($user, Evaluation $evaluation): bool
    {
        if (!$user) {
            return false;
        }

        // Chỉ user có role đúng với level1_approver_role mới có quyền cập nhật work descriptions
        $userRole = $user->roles->first();
        if (!$userRole || $userRole->name !== $evaluation->level1_approver_role) {
            return false;
        }

        // (Có thể bổ sung kiểm tra phòng ban hoặc các điều kiện khác ở đây)
        return true;
    }
}
