<?php
namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Repositories\DepartmentRepository;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    protected $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }

    public function departments(Request $request)
    {
        $departments = $this->departmentRepository->getDepartmentsWithFilters($request->all(), 10);

        return response()->json([
            'status'     => true,
            'message'    => 'Lấy danh sách phòng ban thành công',
            'data'       => DepartmentResource::collection($departments),
            'pagination' => [
                'currentPage'  => $departments->currentPage(),
                'totalPages'   => $departments->lastPage(),
                'totalItems'   => $departments->total(),
                'itemsPerPage' => $departments->perPage(),
            ],
        ]);

    }

    public function summary(Request $request)
    {
        $stats = $this->departmentRepository->getDepartmentStats();

        return response()->json([
            'status'  => true,
            'message' => 'Lấy thông tin tổng quan thành công',
            'data'    => $stats,
        ]);
    }

    public function store(StoreDepartmentRequest $request)
    {
        $department = $this->departmentRepository->create($request->all());

        return response()->json([
            'status'  => true,
            'message' => 'Tạo phòng ban thành công',
            'data'    => new DepartmentResource($department->fresh(['manager', 'employees'])),
        ], 201);
    }

    public function update(UpdateDepartmentRequest $request, $id)
    {
        $department = $this->departmentRepository->update($id, $request->all());

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật phòng ban thành công',
            'data'    => new DepartmentResource($department->fresh(['manager', 'employees'])),
        ]);
    }

    public function destroy($id)
    {
        $this->departmentRepository->delete($id);
        return response()->json([
            'status'  => true,
            'message' => 'Xóa phòng ban thành công',
        ]);
    }
}
