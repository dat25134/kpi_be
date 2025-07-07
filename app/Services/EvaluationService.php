<?php

namespace App\Services;

use App\Repositories\Interfaces\EvaluationRepositoryInterface;
use App\Http\Resources\EvaluationResource;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

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
} 