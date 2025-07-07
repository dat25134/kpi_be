<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\Auth;

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
} 