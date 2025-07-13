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

            // Chỉ cập nhật total_score và final_grade nếu có
            if ($request->filled('total_score')) {
                $evaluationData['total_score'] = $request->input('total_score');
            }
            if ($request->filled('final_grade')) {
                $evaluationData['final_grade'] = $request->input('final_grade');
            }

            $evaluation->update($evaluationData);

            // Lưu evaluation details theo giai đoạn hiện tại
            if ($request->has('evaluation_details')) {
                $this->saveEvaluationDetailsByStage($evaluation->id, $request->input('evaluation_details'), $newStatus);
            }

            // Cập nhật work descriptions (chỉ cấp 1 mới có quyền)
            // TODO: Tạm thời comment để tập trung vào chức năng đánh giá theo từng cấp
            /*
            if ($request->has('work_descriptions')) {
                if (!$this->canUpdateWorkDescriptions($user, $evaluation)) {
                    throw new \InvalidArgumentException('Bạn không có quyền cập nhật work descriptions');
                }
                $this->updateWorkDescriptions($evaluation->id, $request->input('work_descriptions'));
            }
            */

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
    // TODO: Tạm thời comment để tập trung vào chức năng đánh giá theo từng cấp
    /*
    private function updateWorkDescriptions(int $evaluationId, array $workDescriptions): void
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
    */

    /**
     * Tính điểm cuối cùng dựa trên loại đánh giá
     */
    private function calculateFinalScore(array $detail): ?float
    {
        // Ưu tiên đánh giá cấp 2, sau đó cấp 1, cuối cùng là tự đánh giá
        if (isset($detail['level2_score'])) {
            return $detail['level2_score'];
        }
        
        if (isset($detail['level1_score'])) {
            return $detail['level1_score'];
        }
        
        if (isset($detail['self_score'])) {
            return $detail['self_score'];
        }
        
        return null;
    }

    /**
     * Gửi đánh giá (submit)
     */
    public function submitEvaluation(Request $request, int $id): bool
    {
        $evaluation = $this->model->find($id);
        if (!$evaluation) {
            return false;
        }

        // Kiểm tra xem đã có đủ dữ liệu để submit chưa
        if (!$this->validateEvaluationForSubmission($evaluation)) {
            throw new \InvalidArgumentException('Đánh giá chưa đủ dữ liệu để gửi. Vui lòng kiểm tra lại.');
        }

        return $evaluation->update(['status' => 'submitted']);
    }

    /**
     * Kiểm tra tính hợp lệ của đánh giá trước khi submit
     */
    private function validateEvaluationForSubmission(Evaluation $evaluation): bool
    {
        // Kiểm tra xem có evaluation details không
        $hasDetails = $evaluation->evaluationDetails()->exists();
        if (!$hasDetails) {
            return false;
        }

        // Kiểm tra xem có work descriptions không
        $hasWorkDescriptions = $evaluation->workDescriptions()->exists();
        if (!$hasWorkDescriptions) {
            return false;
        }

        // Kiểm tra xem có ít nhất một loại đánh giá (self, level1, hoặc level2) không
        $hasAnyScore = $evaluation->evaluationDetails()
            ->whereNotNull('self_score')
            ->orWhereNotNull('level1_score')
            ->orWhereNotNull('level2_score')
            ->exists();

        return $hasAnyScore;
    }

    /**
     * Kiểm tra quyền cập nhật work descriptions
     */
    // TODO: Tạm thời comment để tập trung vào chức năng đánh giá theo từng cấp
    /*
    private function canUpdateWorkDescriptions($user, Evaluation $evaluation): bool
    {
        if (!$user) {
            return false;
        }

        // Chỉ cấp 1 (trưởng phòng) mới có quyền cập nhật work descriptions
        $userRole = $user->roles->first();
        if (!$userRole || $userRole->name !== 'truongphong') {
            return false;
        }

        // Kiểm tra xem user có phải là trưởng phòng của nhân viên được đánh giá không
        // (có thể thêm logic kiểm tra phòng ban nếu cần)
        return true;
    }
    */
}
