<?php

namespace App\Http\Controllers;

use App\Models\CategoryCriteria;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CategoryCriteriaResource;

class EvaluationCriteriaController extends Controller
{
    /**
     * API trả về tất cả category_criteria, mỗi category chứa các evaluation_criteria đã filter theo role_id
     * GET /api/evaluation-criteria?role_id=1
     */
    public function index(Request $request)
    {
        $roleId = $request->input('role_id');
        $search = $request->input('search');
        if (!$roleId) {
            return response()->json(['message' => 'Vai trò không hợp lệ'], 422);
        }
        $categories = CategoryCriteria::with(['evaluationCriterias' => function ($query) use ($roleId, $search) {
            $query->where('role_id', $roleId)->active()->ordered();
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }
        }])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách tiêu chí đánh giá thành công',
            'data' => CategoryCriteriaResource::collection($categories)
        ]);
    }
} 