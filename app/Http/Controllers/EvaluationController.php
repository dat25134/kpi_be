<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\EvaluationResource;

class EvaluationController extends Controller
{
    /**
     * Lấy danh sách phiếu đánh giá với filter
     */
    public function index(Request $request)
    {
        // Mapping type sang role
        $typeMap = [
            'employee' => 'nhanvien',
            'staff' => 'chuyenvien',
            'deputy' => 'phophong',
            'head' => 'truongphong',
            'personal' => 'personal',
        ];
        $roleType = $typeMap[$request->input('type')] ?? null;
        if (!$roleType) {
            return response()->json(['message' => 'Tham số type không hợp lệ'], 422);
        }

        $isAdmin = $request->user()->hasRole('admin');
        $query = Evaluation::query()->with(['user.roles', 'user.department']);

        // Nếu không phải admin thì filter theo type/role như cũ
        if (!$isAdmin) {
            if ($roleType !== 'personal') {
                $query->whereHas('user.roles', function ($q) use ($roleType) {
                    $q->where('name', $roleType);
                });
            } else {
                $query->where('user_id', Auth::id());
            }
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
        if ($request->filled('rating')) {
            $ratingMap = [
                'excellent' => 'A',
                'good' => 'B',
                'achieved' => 'C',
                'not_achieved' => 'D',
            ];
            $grade = $ratingMap[$request->input('rating')] ?? null;
            if ($grade) {
                $query->where('final_grade', $grade);
            }
        }

        // Lọc theo period (tháng/năm)
        if ($request->filled('period')) {
            $period = $request->input('period');
            if (preg_match('/^(\d{1,2})\/(\d{4})$/', $period, $matches)) {
                $month = (int)$matches[1];
                $year = (int)$matches[2];
                $query->where('month', $month)->where('year', $year);
            }
        }

        // Phân trang
        $page = (int)($request->input('page', 1));
        $pageSize = (int)($request->input('pageSize', 10));
        $evaluations = $query->orderByDesc('year')->orderByDesc('month')->paginate($pageSize, ['*'], 'page', $page);

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
    }

    public function show($id)
    {
        $evaluation = \App\Models\Evaluation::with(['user.roles', 'user.department', 'evaluationDetails.criteria', 'workDescriptions'])
            ->findOrFail($id);
        return new \App\Http\Resources\EvaluationResource($evaluation);
    }
} 