<?php

namespace App\Http\Controllers;

use App\Models\CategoryCriteria;
use App\Models\Role;
use App\Models\EvaluationCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CategoryCriteriaResource;
use App\Http\Resources\EvaluationCriteriaResource;
use App\Http\Requests\EvaluationCriteria\StoreCategoryCriteriaRequest;
use App\Http\Requests\EvaluationCriteria\StoreCriteriaRequest;
use App\Http\Requests\EvaluationCriteria\UpdateCriteriaRequest;

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

    public function storeCategory(StoreCategoryCriteriaRequest $request)
    {
        $category = CategoryCriteria::create($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Thêm danh mục tiêu chí đánh giá thành công',
            'data' => new CategoryCriteriaResource($category)
        ]);
    }

    public function updateCategory(StoreCategoryCriteriaRequest $request, $id)
    {
        $category = CategoryCriteria::findOrFail($id);
        $category->update($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật danh mục tiêu chí đánh giá thành công',
            'data' => new CategoryCriteriaResource($category)
        ]);
    }

    public function destroyCategory($id)
    {
        $category = CategoryCriteria::findOrFail($id);
        
        // Kiểm tra xem category có evaluation criteria nào không
        $hasEvaluationCriteria = $category->evaluationCriterias()->exists();
        
        if ($hasEvaluationCriteria) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa danh mục này vì đang có tiêu chí đánh giá được sử dụng. Vui lòng xóa tất cả tiêu chí đánh giá trước khi xóa danh mục.',
            ], 400);
        }
        
        $category->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Xóa danh mục tiêu chí đánh giá thành công',
        ]);
    }

    public function storeCriteria(StoreCriteriaRequest $request)
    {
        // Luôn tự động tính order
        $maxOrder = \App\Models\EvaluationCriteria::where('role_id', $request->role_id)
            ->where('category_criteria_id', $request->category_criteria_id)
            ->max('order');
        $order = $maxOrder ? $maxOrder + 1 : 1;

        $dataCreate = [
            'role_id' => $request->role_id,
            'category_criteria_id' => $request->category_criteria_id,
            'name' => $request->name,
            'description' => $request->description,
            'max_score' => $request->max_score,
            'weight' => 1,
            'order' => $order,
            'is_active' => $request->is_active,
        ];
        $criteria = EvaluationCriteria::create($dataCreate);

        // Load relationships for the response
        $criteria->load(['role', 'categoryCriteria']);

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm tiêu chí đánh giá thành công',
            'data' => new EvaluationCriteriaResource($criteria)
        ]);
    }

    public function updateCriteria(UpdateCriteriaRequest $request, $id)
    {
        $criteria = EvaluationCriteria::findOrFail($id);
        $dataUpdate = [
            'name' => $request->name,
            'description' => $request->description,
            'max_score' => $request->max_score,
            'is_active' => $request->is_active,
        ];
        $criteria->update($dataUpdate);
        
        // Load relationships for the response
        $criteria->load(['role', 'categoryCriteria']);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật tiêu chí đánh giá thành công',
            'data' => new EvaluationCriteriaResource($criteria)
        ]);
    }

    public function destroyCriteria($id)
    {
        $criteria = EvaluationCriteria::findOrFail($id);
        
        // Kiểm tra xem criteria có evaluation details nào không
        $hasEvaluationDetails = $criteria->evaluationDetails()->exists();
        
        if ($hasEvaluationDetails) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa tiêu chí này vì đang có chi tiết đánh giá được sử dụng. Vui lòng xóa tất cả chi tiết đánh giá trước khi xóa tiêu chí.',
            ], 400);
        }
        
        $criteria->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Xóa tiêu chí đánh giá thành công',
        ]);
    }
} 