<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EmployeeResource;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Arr;
use App\Services\PasswordGeneratorService;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    /**
     * Lấy danh sách nhân viên với filter và phân trang
     */
    public function employees(Request $request)
    {
        $employees = $this->userRepository->getEmployeesWithFilters($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách nhân viên thành công',
            'data' => [
                'employees' => EmployeeResource::collection($employees),
                'pagination' => [
                    'currentPage' => $employees->currentPage(),
                    'totalPages' => $employees->lastPage(),
                    'totalItems' => $employees->total(),
                    'itemsPerPage' => $employees->perPage(),
                ],
            ],
        ]);
    }

    public function allEmployees(Request $request)
    {
        $employees = $this->userRepository->all();
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách nhân viên thành công',
            'data' => EmployeeResource::collection($employees),
        ]);
    }

    /**
     * Lấy thống kê tổng quan về nhân viên
     */
    public function summary(Request $request)
    {
        $stats = $this->userRepository->getEmployeeStats();

        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê nhân viên thành công',
            'data' => [
                'totalEmployees' => $stats['totalEmployees'],
                'activeEmployees' => $stats['activeEmployees'],
                'inactiveEmployees' => $stats['inactiveEmployees'],
                'joinedThisMonth' => $stats['joinedThisMonth'],
                'departmentStats' => $stats['departmentStats'],
                'roleStats' => $stats['roleStats'],
            ],
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $userData = $request->only('name', 'email', 'phone', 'cccd', 'departmentId', 'roleName', 'joinDate');
        $userInfoData = $request->only('salary', 'address', 'birthDate', 'gender', 'education', 'experience', 'skills');
        $result = $this->userRepository->createEmployee($userData, $userInfoData);
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo nhân viên thành công. Mật khẩu đăng nhập đã được gửi đến email ' . $result->email,
                'data' => new EmployeeResource($result),
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tạo nhân viên thất bại.',
            ], 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        $userData = $request->only('name', 'email', 'phone', 'cccd', 'departmentId','roleName', 'joinDate');
        $userInfoData = $request->only('salary', 'address', 'birthDate', 'gender', 'education', 'experience', 'skills');
        $result = $this->userRepository->updateEmployee($id, $userData, $userInfoData);

        if ($result) {

        return response()->json([
                'success' => true,
                'message' => 'Cập nhật nhân viên thành công',
                'data' => new EmployeeResource($result),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật nhân viên thất bại.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $result = $this->userRepository->deleteEmployee($id);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa nhân viên thành công',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Xóa nhân viên thất bại.',
            ], 500);
        }
    }

    public function details($id)
    {
        $user = $this->userRepository->details($id);
        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin nhân viên thành công',
            'data' => new EmployeeResource($user),
        ]);
    }

    public function manager(Request $request)
    {
        $truongphong = $this->userRepository->manager();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách trưởng phòng thành công',
            'data' => EmployeeResource::collection($truongphong),
        ]);
    }

    /**
     * Import nhân viên từ file CSV/Excel
     */
    public function importEmployees(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xls,xlsx',
        ], [
            'file.required' => 'Vui lòng chọn file.',
            'file.file' => 'File không hợp lệ.',
            'file.mimes' => 'Chỉ hỗ trợ file CSV, XLS, XLSX.',
        ]);

        $file = $request->file('file');
        $rows = [];
        try {
            $rows = Excel::toArray([], $file)[0]; // Lấy sheet đầu tiên
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể đọc file: ' . $e->getMessage(),
            ], 422);
        }
        if (empty($rows) || count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File không có dữ liệu hoặc thiếu header.',
            ], 422);
        }
        $header = array_map(fn($h) => trim($h), $rows[0]);
        $dataRows = array_slice($rows, 1);
        $errors = [];
        $imported = 0;
        foreach ($dataRows as $index => $row) {
            $rowData = array_combine($header, $row + array_fill(0, count($header), null));
            $line = $index + 2; // Dòng thực tế trong file (tính cả header)
            // Validate các trường bắt buộc
            $name = trim($rowData['name'] ?? '');
            $email = trim($rowData['email'] ?? '');
            $cccd = trim($rowData['cccd'] ?? '');
            $roleName = trim($rowData['roleName'] ?? '');
            if (!$name) {
                $errors[] = [ 'row' => $line, 'field' => 'name', 'error' => 'Thiếu tên nhân viên' ];
                continue;
            }
            if (!$email) {
                $errors[] = [ 'row' => $line, 'field' => 'email', 'error' => 'Thiếu email' ];
                continue;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = [ 'row' => $line, 'field' => 'email', 'error' => 'Email không hợp lệ' ];
                continue;
            }
            if (!$cccd || strlen($cccd) < 4) {
                $errors[] = [ 'row' => $line, 'field' => 'cccd', 'error' => 'Thiếu hoặc không đủ 4 số cuối CCCD' ];
                continue;
            }
            if (!$roleName) {
                $errors[] = [ 'row' => $line, 'field' => 'roleName', 'error' => 'Thiếu vai trò' ];
                continue;
            }
            // Validate roleName tồn tại
            if (!\Spatie\Permission\Models\Role::where('name', $roleName)->exists()) {
                $errors[] = [ 'row' => $line, 'field' => 'roleName', 'error' => 'Vai trò không tồn tại trong hệ thống' ];
                continue;
            }
            // Kiểm tra trùng email, cccd trong DB
            if (\App\Models\User::where('email', $email)->exists()) {
                $errors[] = [ 'row' => $line, 'field' => 'email', 'error' => 'Email đã tồn tại trong hệ thống' ];
                continue;
            }
            if ($cccd && \App\Models\User::where('cccd', $cccd)->exists()) {
                $errors[] = [ 'row' => $line, 'field' => 'cccd', 'error' => 'CCCD đã tồn tại trong hệ thống' ];
                continue;
            }
            // Sinh password
            $password = PasswordGeneratorService::generatePasswordFromNameAndCCCD($name, $cccd);

            if (!$password) {
                $errors[] = [ 'row' => $line, 'field' => 'password', 'error' => 'Không thể sinh mật khẩu từ họ và CCCD' ];
                continue;
            }
            // Chuẩn bị dữ liệu cho createEmployee
            $userData = [
                'name' => $name,
                'email' => $email,
                'phone' => trim($rowData['phone'] ?? ''),
                'cccd' => $cccd,
                'departmentId' => $rowData['departmentId'] ?? null,
                'roleName' => $roleName,
                'joinDate' => $rowData['joinDate'] ?? null,
                'password' => $password, // Truyền password custom
            ];
            $userInfoData = [
                'salary' => $rowData['salary'] ?? null,
                'address' => $rowData['address'] ?? null,
                'birthDate' => $rowData['birthDate'] ?? null,
                'gender' => $rowData['gender'] ?? null,
                'education' => $rowData['education'] ?? null,
                'experience' => $rowData['experience'] ?? null,
                'skills' => isset($rowData['skills']) ? array_map('trim', explode(',', $rowData['skills'])) : [],
            ];
            try {
                // Gọi createEmployee, nhưng cần sửa lại để nhận password custom
                $this->userRepository->createEmployee($userData, $userInfoData, true);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = [ 'row' => $line, 'field' => 'system', 'error' => 'Lỗi hệ thống: ' . $e->getMessage() ];
            }
        }
        if ($errors) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi khi import file',
                'imported' => $imported,
                'errors' => $errors
            ], 422);
        }
        return response()->json([
            'success' => true,
            'message' => 'Import thành công',
            'imported' => $imported
        ]);
    }

    /**
     * Reset mật khẩu cho nhân viên, gửi email mật khẩu mới
     * POST /api/employees/{id}/reset-password
     */
    public function resetPassword($id)
    {
        try {
            $user = $this->userRepository->resetEmployeePassword($id);
            return response()->json([
                'message' => 'Đã gửi email đặt lại mật khẩu cho nhân viên.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể reset mật khẩu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template file để import employees
     * GET /api/employees/template
     */
    public function downloadTemplate()
    {
        $filePath = public_path('template-import-employees.xlsx');
        
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Template file không tồn tại'
            ], 404);
        }

        return response()->download($filePath, 'template-import-employees.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template-import-employees.xlsx"'
        ]);
    }
} 