<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\ModulePermission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Quản lý nhân sự
            [ 'name' => 'hr.view', 'display_name' => 'Xem danh sách nhân viên', 'module' => 'HR', 'category' => 'Xem', 'description' => 'Xem danh sách và thông tin nhân viên' ],
            [ 'name' => 'hr.create', 'display_name' => 'Thêm nhân viên mới', 'module' => 'HR', 'category' => 'Thêm', 'description' => 'Tạo mới nhân viên' ],
            [ 'name' => 'hr.edit', 'display_name' => 'Chỉnh sửa thông tin nhân viên', 'module' => 'HR', 'category' => 'Sửa', 'description' => 'Chỉnh sửa thông tin nhân viên' ],
            [ 'name' => 'hr.delete', 'display_name' => 'Xóa nhân viên', 'module' => 'HR', 'category' => 'Xóa', 'description' => 'Xóa nhân viên khỏi hệ thống' ],

            // Phòng ban (chỉ cần 1 quyền quản lý toàn bộ)
            [ 'name' => 'department.manage', 'display_name' => 'Quản lý phòng ban', 'module' => 'Department', 'category' => 'Quản lý', 'description' => 'Toàn quyền quản lý phòng ban (xem, thêm, sửa, xóa, phân công trưởng phòng)' ],

            // Quản lý dự án/công việc
            [ 'name' => 'project.view_all', 'display_name' => 'Xem toàn bộ dự án/công việc', 'module' => 'Project', 'category' => 'Xem', 'description' => 'Xem tất cả dự án/công việc trong hệ thống' ],
            [ 'name' => 'project.view_related', 'display_name' => 'Xem dự án/công việc liên quan', 'module' => 'Project', 'category' => 'Xem', 'description' => 'Xem các dự án/công việc mà mình có liên quan' ],
            [ 'name' => 'project.create', 'display_name' => 'Tạo mới dự án/công việc', 'module' => 'Project', 'category' => 'Thêm', 'description' => 'Tạo mới dự án/công việc' ],
            [ 'name' => 'project.create_sub', 'display_name' => 'Tạo mới dự án/công việc con', 'module' => 'Project', 'category' => 'Thêm', 'description' => 'Tạo mới dự án/công việc con' ],
            [ 'name' => 'project.edit', 'display_name' => 'Chỉnh sửa dự án/công việc', 'module' => 'Project', 'category' => 'Sửa', 'description' => 'Chỉnh sửa thông tin dự án/công việc' ],
            [ 'name' => 'project.update_progress', 'display_name' => 'Cập nhật tiến độ dự án/công việc', 'module' => 'Project', 'category' => 'Cập nhật', 'description' => 'Cập nhật tiến độ dự án/công việc' ],
            [ 'name' => 'project.export_report', 'display_name' => 'Xuất báo cáo dự án/công việc', 'module' => 'Project', 'category' => 'Xuất', 'description' => 'Xuất báo cáo dự án/công việc' ],

            // Đánh giá
            [ 'name' => 'evaluation.view', 'display_name' => 'Xem phiếu đánh giá', 'module' => 'Evaluation', 'category' => 'Xem', 'description' => 'Xem phiếu đánh giá' ],
            [ 'name' => 'evaluation.save', 'display_name' => 'Tự đánh giá', 'module' => 'Evaluation', 'category' => 'Tự đánh giá', 'description' => 'Tự đánh giá' ],
            [ 'name' => 'evaluation.approve', 'display_name' => 'Duyệt phiếu đánh giá', 'module' => 'Evaluation', 'category' => 'Duyệt', 'description' => 'Duyệt phiếu đánh giá (chỉ dành cho cấp Trưởng phòng, Phó Phòng, Phó Chủ tịch, Chủ tịch)' ],
            [ 'name' => 'evaluation_criteria.manage', 'display_name' => 'Quản lý tiêu chí đánh giá', 'module' => 'Evaluation', 'category' => 'Quản lý', 'description' => 'Toàn quyền xử lý tiêu chí đánh giá (xem, thêm, sửa, xóa)' ],

            // Hệ thống
            [ 'name' => 'system.grant_permission', 'display_name' => 'Cấp quyền cho người dùng', 'module' => 'System', 'category' => 'Cấp quyền', 'description' => 'Toàn quyền cấp quyền cho người dùng' ],
            [ 'name' => 'system.view_log', 'display_name' => 'Xem log hệ thống', 'module' => 'System', 'category' => 'Xem', 'description' => 'Xem nhật ký hệ thống' ],

            // Báo cáo
            [ 'name' => 'report.view_dashboard', 'display_name' => 'Xem báo cáo dashboard', 'module' => 'Report', 'category' => 'Xem', 'description' => 'Xem báo cáo dashboard tổng hợp' ],
            [ 'name' => 'report.export', 'display_name' => 'Xuất báo cáo', 'module' => 'Report', 'category' => 'Xuất', 'description' => 'Xuất báo cáo ra file' ],
        ];

        foreach ($permissions as $permission) {
            $modulePermission = ModulePermission::where('name', $permission['module'])->first();
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'],
                    'module_permission_id' => $modulePermission ? $modulePermission->id : null,
                    'category' => $permission['category'],
                    'description' => $permission['description'],
                    'guard_name' => 'web',
                ]
            );
        }

        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->syncPermissions(Permission::all());
    }
} 