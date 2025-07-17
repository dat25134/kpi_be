<?php

namespace App\Services;

use App\Repositories\Interfaces\EvaluationRepositoryInterface;
use App\Http\Resources\EvaluationResource;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EvaluationService
{
    protected $evaluationRepository;

    public function __construct(EvaluationRepositoryInterface $evaluationRepository)
    {
        $this->evaluationRepository = $evaluationRepository;
    }

    /**
     * Lấy danh sách phiếu đánh giá với filter
     */
    public function getEvaluationsWithFilters(Request $request, bool $isAdmin): JsonResponse
    {
        try {
            $evaluations = $this->evaluationRepository->getEvaluationsWithFilters($request, $isAdmin);
            
            // Định dạng dữ liệu trả về bằng resource
            $data = EvaluationResource::collection($evaluations->getCollection());

            return response()->json([
                'message' => 'Lấy danh sách phiếu đánh giá thành công',
                'data' => $data,
                'pagination' => [
                    'currentPage'  => $evaluations->currentPage(),
                    'totalPages'   => $evaluations->lastPage(),
                    'totalItems'   => $evaluations->total(),
                    'itemsPerPage' => $evaluations->perPage(),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi lấy danh sách phiếu đánh giá'], 500);
        }
    }

    /**
     * Lấy chi tiết phiếu đánh giá
     */
    public function getEvaluationById(int $id): JsonResponse
    {
        try {
            $evaluation = $this->evaluationRepository->findById($id);
            
            if (!$evaluation) {
                return response()->json(['message' => 'Không tìm thấy phiếu đánh giá'], 404);
            }

            return response()->json([
                'message' => 'Lấy chi tiết phiếu đánh giá thành công',
                'data' => new EvaluationResource($evaluation)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi lấy chi tiết phiếu đánh giá'], 500);
        }
    }

    /**
     * Tạo phiếu đánh giá mới
     */
    public function createEvaluation(array $data): JsonResponse
    {
        try {
            $evaluation = $this->evaluationRepository->create($data);
            
            return response()->json([
                'message' => 'Tạo phiếu đánh giá thành công',
                'data' => new EvaluationResource($evaluation)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi tạo phiếu đánh giá'], 500);
        }
    }

    /**
     * Cập nhật phiếu đánh giá
     */
    public function updateEvaluation(int $id, array $data): JsonResponse
    {
        try {
            $success = $this->evaluationRepository->update($id, $data);
            
            if (!$success) {
                return response()->json(['message' => 'Không tìm thấy phiếu đánh giá để cập nhật'], 404);
            }

            $evaluation = $this->evaluationRepository->findById($id);
            
            return response()->json([
                'message' => 'Cập nhật phiếu đánh giá thành công',
                'data' => new EvaluationResource($evaluation)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật phiếu đánh giá'], 500);
        }
    }

    /**
     * Xóa phiếu đánh giá
     */
    public function deleteEvaluation(int $id): JsonResponse
    {
        try {
            $evaluation = $this->evaluationRepository->findById($id);
            if (!$evaluation) {
                return response()->json(['message' => 'Không tìm thấy phiếu đánh giá để xóa'], 404);
            }
            // Kiểm tra quyền sở hữu
            $currentUser = request()->user();
            if (!$currentUser || $evaluation->user_id !== $currentUser->id) {
                return response()->json([
                    'message' => 'Bạn không có quyền xóa phiếu đánh giá này'
                ], 403);
            }
            if (!in_array($evaluation->status, ['draft', 'submitted'])) {
                return response()->json([
                    'message' => 'Bạn không thể xóa khi phiếu đã được duyệt'
                ], 403);
            }
            $success = $this->evaluationRepository->delete($id);
            if (!$success) {
                return response()->json(['message' => 'Không tìm thấy phiếu đánh giá để xóa'], 404);
            }
            return response()->json(['message' => 'Xóa phiếu đánh giá thành công']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa phiếu đánh giá'], 500);
        }
    }

    /**
     * Kiểm tra xem đã có phiếu đánh giá cho user trong period chưa
     */
    public function checkExistingEvaluation(int $userId, int $month, int $year): bool
    {
        $evaluation = $this->evaluationRepository->findByUserAndPeriod($userId, $month, $year);
        return $evaluation !== null;
    }

    /**
     * Lưu đánh giá với các chi tiết đánh giá và work descriptions
     */
    public function saveEvaluation(Request $request, int $id): JsonResponse
    {
        try {
            // Lấy user hiện tại
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Không tìm thấy thông tin người dùng'], 401);
            }

            $success = $this->evaluationRepository->saveEvaluation($request, $id, $user);
            
            if (!$success) {
                return response()->json(['message' => 'Không tìm thấy phiếu đánh giá để lưu'], 404);
            }

            $evaluation = $this->evaluationRepository->findById($id);
            
            return response()->json([
                'message' => 'Lưu đánh giá thành công',
                'data' => new EvaluationResource($evaluation)
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi lưu đánh giá'], 500);
        }
    }

    /**
     * Cập nhật work descriptions cho evaluation
     */
    public function updateWorkDescriptions(int $evaluationId, array $workDescriptions, $user): JsonResponse
    {
        try {
            $evaluation = $this->evaluationRepository->findById($evaluationId);
            if (!$evaluation) {
                return response()->json(['message' => 'Không tìm thấy phiếu đánh giá'], 404);
            }
            if (!$this->evaluationRepository->canUpdateWorkDescriptions($user, $evaluation)) {
                return response()->json(['message' => 'Bạn không có quyền cập nhật Bảng mô tả công việc cho đánh giá'], 403);
            }
            $this->evaluationRepository->updateWorkDescriptions($evaluationId, $workDescriptions);
            return response()->json([
                'status' => true,
                'message' => 'Cập nhật Bảng mô tả công việc cho đánh giá thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật Bảng mô tả công việc cho đánh giá'], 500);
        }
    }

    /**
     * Tạo phiếu đánh giá cho user hiện tại (thủ công)
     */
    public function manualCreateEvaluation($user, $month = null, $year = null)
    {
        try {
            // $month, $year đã được validate ở form request
            $now = Carbon::create($year, $month, 25, 0, 0, 0);

            // Kiểm tra đã có phiếu đánh giá chưa (kể cả soft delete)
            $exists = \App\Models\Evaluation::withTrashed()
                ->where('user_id', $user->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('deleted_at', null)
                ->exists();
            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Phiếu đánh giá đã tồn tại không thể tạo'
                ], 409);
            }

            // Lấy các task user là collaborator
            $taskIdsAsCollaborator = DB::table('task_collaborators')
                ->where('user_id', $user->id)
                ->pluck('task_id')
                ->toArray();

            // Lấy công việc cần đánh giá với tất cả vai trò liên quan
            $tasks = \App\Models\Task::where(function ($q) use ($user, $taskIdsAsCollaborator) {
                    $q->where('main_assignee_id', $user->id)
                      ->orWhere('assigner_id', $user->id)
                      ->orWhere('created_by', $user->id);
                    if (!empty($taskIdsAsCollaborator)) {
                        $q->orWhereIn('id', $taskIdsAsCollaborator);
                    }
                })
                ->where(function ($q) use ($month, $year, $now) {
                    $q->where(function ($q2) use ($month, $year) {
                        $q2->where('status', 'completed')
                            ->whereMonth('completed_at', $month)
                            ->whereYear('completed_at', $year);
                    })
                    ->orWhere(function ($q2) use ($now) {
                        $q2->where('status', '!=', 'completed')
                            ->where('due_date', '<', $now);
                    });
                })
                ->distinct()
                ->get();

            if ($tasks->count() === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không có công việc nào cần đánh giá trong tháng này.'
                ], 200);
            }

            DB::beginTransaction();

            // Tạo phiếu đánh giá
            $role = $user->roles->first()->name;
            $evaluationData = $this->getEvaluationData($user, $month, $year, $role);
            $evaluation = \App\Models\Evaluation::firstOrCreate($evaluationData, [
                'department' => $user->department->name ?? null,
                'status' => 'draft',
            ]);

            // Bổ sung evaluation_details cho các tiêu chí hiện hành nếu chưa có
            $criteriaIds = \App\Models\EvaluationCriteria::where('is_active', true)
                ->where('role_id', $user->roles->first()->id)
                ->pluck('id')
                ->toArray();
            $existingDetailIds = \App\Models\EvaluationDetail::where('evaluation_id', $evaluation->id)
                ->pluck('criteria_id')
                ->toArray();
            $missingCriteriaIds = array_diff($criteriaIds, $existingDetailIds);
            foreach ($missingCriteriaIds as $criteriaId) {
                \App\Models\EvaluationDetail::create([
                    'evaluation_id' => $evaluation->id,
                    'criteria_id' => $criteriaId,
                ]);
            }

            // Tạo work descriptions cho từng task
            foreach ($tasks as $task) {
                $result_level = 1;
                if ($task->status == 'completed' && $task->completed_at <= $task->due_date) {
                    $result_level = 3;
                } elseif ($task->status == 'completed' && $task->completed_at > $task->due_date) {
                    $result_level = 2;
                }
                $qualityWeight = $task->quality_weight ?? 2;
                \App\Models\WorkDescription::updateOrCreate([
                    'evaluation_id' => $evaluation->id,
                    'task_id' => $task->id,
                ], [
                    'task_title' => $task->content,
                    'task_description' => $task->description ?? null,
                    'task_status' => $task->status,
                    'task_start_date' => $task->start_date,
                    'task_due_date' => $task->due_date,
                    'task_weight' => $task->weight,
                    'unit' => 'Thời gian HT',
                    'target' => $task->due_date,
                    'quality_weight' => $qualityWeight,
                    'result_level' => $result_level,
                    'result_score' => ($result_level * $qualityWeight) / 5,
                    'final_score' => ($result_level * $qualityWeight) / 5 * $task->weight,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Tạo phiếu đánh giá thành công',
                'evaluation_id' => $evaluation->id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi tạo phiếu đánh giá',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Copy từ AutoCreateMonthlyEvaluations
    public function getEvaluationData($user, $month, $year, $role)
    {
        if ($role == 'nhanvien') {
            $creatorRole = 'nhanvien';
            $level1ApproverRole = 'truongphong';
            $level2ApproverRole = 'chutich';
        } elseif ($role == 'truongphong') {
            $creatorRole = 'truongphong';
            $level1ApproverRole = 'phochutich';
            $level2ApproverRole = 'chutich';
        } elseif ($role == 'phophong') {
            $creatorRole = 'phophong';
            $level1ApproverRole = 'truongphong';
            $level2ApproverRole = 'phochutich';
        } elseif ($role == 'chuyenvien') {
            $creatorRole = 'chuyenvien';
            $level1ApproverRole = 'truongphong';
            $level2ApproverRole = 'chutich';
        }
        return [
            'user_id' => $user->id,
            'month'   => $month,
            'year'    => $year,
            'creator_role' => $creatorRole,
            'level1_approver_role' => $level1ApproverRole,
            'level2_approver_role' => $level2ApproverRole,
        ];
    }
} 