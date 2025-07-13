<?php

namespace App\Services;

use App\Repositories\Interfaces\EvaluationRepositoryInterface;
use App\Http\Resources\EvaluationResource;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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
            // Validate request data
            $this->validateSaveEvaluationRequest($request);

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
     * Validate dữ liệu request cho việc lưu đánh giá
     */
    private function validateSaveEvaluationRequest(Request $request): void
    {
        $status = $request->input('status', 'draft');
        $user = $request->user();
        
        $rules = [
            'status' => 'sometimes|in:draft,submitted,level1_approved,level2_approved,completed',
            'total_score' => 'sometimes|numeric|min:0|max:100',
            'final_grade' => 'sometimes|in:A,B,C,D',
        ];

        // Validation cho evaluation_details theo giai đoạn
        if ($request->has('evaluation_details')) {
            $rules['evaluation_details'] = 'array';
            $rules['evaluation_details.*.criteria_id'] = 'required|exists:evaluation_criteria,id';
            
            switch ($status) {
                case 'draft':
                    $rules['evaluation_details.*.self_score'] = 'sometimes|numeric|min:0';
                    $rules['evaluation_details.*.self_comment'] = 'sometimes|string';
                    break;
                    
                case 'level1_approved':
                    $rules['evaluation_details.*.level1_score'] = 'sometimes|numeric|min:0';
                    $rules['evaluation_details.*.level1_comment'] = 'sometimes|string';
                    break;
                    
                case 'level2_approved':
                    $rules['evaluation_details.*.level2_score'] = 'sometimes|numeric|min:0';
                    $rules['evaluation_details.*.level2_comment'] = 'sometimes|string';
                    break;
                    
                default:
                    // Các giai đoạn khác không cho phép thay đổi evaluation details
                    $rules['evaluation_details'] = 'prohibited';
            }
        }

        // Validation cho work_descriptions (chỉ cấp 1 mới có quyền)
        // TODO: Tạm thời comment để tập trung vào chức năng đánh giá theo từng cấp
        /*
        if ($request->has('work_descriptions')) {
            // Kiểm tra quyền cập nhật work descriptions
            if (!$this->canUpdateWorkDescriptions($user)) {
                throw new \InvalidArgumentException('Bạn không có quyền cập nhật work descriptions. Chỉ trưởng phòng mới có quyền này.');
            }
            
            $rules['work_descriptions'] = 'array';
            $rules['work_descriptions.*.id'] = 'required|exists:work_descriptions,id';
            $rules['work_descriptions.*.result_level'] = 'sometimes|integer|min:1|max:4';
            $rules['work_descriptions.*.quality_weight'] = 'sometimes|integer|min:1|max:5';
        }
        */

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    /**
     * Kiểm tra quyền cập nhật work descriptions
     */
    // TODO: Tạm thời comment để tập trung vào chức năng đánh giá theo từng cấp
    /*
    private function canUpdateWorkDescriptions($user): bool
    {
        if (!$user) {
            return false;
        }

        // Chỉ cấp 1 (trưởng phòng) mới có quyền cập nhật work descriptions
        $userRole = $user->roles->first();
        return $userRole && $userRole->name === 'truongphong';
    }
    */
} 