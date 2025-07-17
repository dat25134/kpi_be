<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EvaluationService;
use App\Http\Requests\StoreEvaluationRequest;
use App\Http\Requests\UpdateWorkDescriptionsRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ManualCreateEvaluationRequest;

class EvaluationController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Lấy danh sách phiếu đánh giá với filter
     */
    public function index(Request $request)
    {
        $isAdmin = $request->user()->hasRole('admin');
        return $this->evaluationService->getEvaluationsWithFilters($request, $isAdmin);
    }

    /**
     * Lấy chi tiết phiếu đánh giá
     */
    public function show($id)
    {
        return $this->evaluationService->getEvaluationById((int) $id);
    }

    /**
     * Tạo phiếu đánh giá mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'status' => 'required|in:draft,submitted,approved,rejected',
        ]);

        return $this->evaluationService->createEvaluation($validated);
    }

    /**
     * Cập nhật phiếu đánh giá
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'department_id' => 'sometimes|exists:departments,id',
            'month' => 'sometimes|integer|between:1,12',
            'year' => 'sometimes|integer|min:2020',
            'status' => 'sometimes|in:draft,submitted,approved,rejected',
            'total_score' => 'sometimes|numeric|min:0',
            'final_grade' => 'sometimes|in:A,B,C,D',
        ]);

        return $this->evaluationService->updateEvaluation((int) $id, $validated);
    }

    /**
     * Xóa phiếu đánh giá
     */
    public function destroy($id)
    {
        return $this->evaluationService->deleteEvaluation((int) $id);
    }

    /**
     * Lưu đánh giá với các chi tiết đánh giá và work descriptions
     */
    public function save(StoreEvaluationRequest $request, $id)
    {
        $user = $request->user();
        $canSelfEvaluate = $user->can('evaluation.save');
        $canApprove = $user->can('evaluation.approve');
        $status = $request->input('status');

        if (!$canSelfEvaluate && !$canApprove) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này!'
            ], 403);
        }
        

        // Các trạng thái cho từng quyền
        $selfStatuses = ['draft', 'submitted'];
        $approveStatuses = ['level1_approved', 'level2_approved', 'completed'];

        if ($status) {
            if (in_array($status, $selfStatuses) && !$canSelfEvaluate) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn không có quyền tự đánh giá!'
                ], 403);
            }
            if (in_array($status, $approveStatuses) && !$canApprove) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn không có quyền duyệt phiếu đánh giá!'
                ], 403);
            }
            // Nếu status không thuộc bất kỳ nhóm nào, không cho phép
            if (!in_array($status, array_merge($selfStatuses, $approveStatuses))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Trạng thái không hợp lệ!'
                ], 422);
            }
        } else {
            // Nếu không truyền status, không cho phép
            return response()->json([
                'status' => false,
                'message' => 'Thiếu trạng thái đánh giá!'
            ], 422);
        }

        // Gọi service xử lý như cũ
        return $this->evaluationService->saveEvaluation($request, (int) $id);
    }

    /**
     * Cập nhật work descriptions cho evaluation
     */
    public function updateWorkDescriptions(UpdateWorkDescriptionsRequest $request, $id)
    {
        $user = $request->user();
        $result = $this->evaluationService->updateWorkDescriptions($id, $request->input('work_descriptions', []), $user);
        return $result;
    }

    public function manualCreateEvaluation(ManualCreateEvaluationRequest $request)
    {
        $user = $request->user();
        $month = $request->input('month');
        $year = $request->input('year');
        $result = $this->evaluationService->manualCreateEvaluation($user, $month, $year);
        return $result;
    }
} 