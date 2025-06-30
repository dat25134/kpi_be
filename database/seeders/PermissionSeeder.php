<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Quản lý nhân sự
            [ 'name' => 'hr.view', 'display_name' => 'Xem danh sách nhân viên', 'module' => 'HR', 'category' => 'Xem', 'description' => 'Xem thông tin cơ bản của nhân viên' ],
            [ 'name' => 'hr.create', 'display_name' => 'Thêm nhân viên mới', 'module' => 'HR', 'category' => 'Thêm', 'description' => 'Tạo hồ sơ nhân viên mới' ],
            [ 'name' => 'hr.edit', 'display_name' => 'Chỉnh sửa thông tin nhân viên', 'module' => 'HR', 'category' => 'Sửa', 'description' => 'Cập nhật thông tin nhân viên' ],
            [ 'name' => 'hr.delete', 'display_name' => 'Xóa nhân viên', 'module' => 'HR', 'category' => 'Xóa', 'description' => 'Xóa hồ sơ nhân viên khỏi hệ thống' ],
            [ 'name' => 'hr.role', 'display_name' => 'Quản lý chức vụ', 'module' => 'HR', 'category' => 'Quản lý', 'description' => 'Phân công và thay đổi chức vụ' ],
            // Quản lý phòng ban
            [ 'name' => 'department.view', 'display_name' => 'Xem danh sách phòng ban', 'module' => 'Department', 'category' => 'Xem', 'description' => 'Xem thông tin các phòng ban' ],
            [ 'name' => 'department.create', 'display_name' => 'Thêm phòng ban mới', 'module' => 'Department', 'category' => 'Thêm', 'description' => 'Tạo phòng ban mới' ],
            [ 'name' => 'department.edit', 'display_name' => 'Chỉnh sửa thông tin phòng ban', 'module' => 'Department', 'category' => 'Sửa', 'description' => 'Cập nhật thông tin phòng ban' ],
            [ 'name' => 'department.delete', 'display_name' => 'Xóa phòng ban', 'module' => 'Department', 'category' => 'Xóa', 'description' => 'Xóa phòng ban khỏi hệ thống' ],
            [ 'name' => 'department.assign_manager', 'display_name' => 'Phân công trưởng phòng', 'module' => 'Department', 'category' => 'Phân công', 'description' => 'Chỉ định trưởng phòng' ],
            // Quản lý dự án/công việc
            [ 'name' => 'project.view', 'display_name' => 'Xem danh sách dự án/công việc', 'module' => 'Project', 'category' => 'Xem', 'description' => 'Xem thông tin các dự án/công việc' ],
            [ 'name' => 'project.create', 'display_name' => 'Tạo dự án/công việc mới', 'module' => 'Project', 'category' => 'Thêm', 'description' => 'Khởi tạo dự án/công việc mới' ],
            [ 'name' => 'project.edit', 'display_name' => 'Chỉnh sửa dự án/công việc', 'module' => 'Project', 'category' => 'Sửa', 'description' => 'Cập nhật thông tin dự án/công việc' ],
            [ 'name' => 'project.delete', 'display_name' => 'Xóa dự án/công việc', 'module' => 'Project', 'category' => 'Xóa', 'description' => 'Xóa dự án/công việc khỏi hệ thống' ],
            [ 'name' => 'project.assign', 'display_name' => 'Phân công nhiệm vụ', 'module' => 'Project', 'category' => 'Phân công', 'description' => 'Giao việc cho thành viên' ],
            [ 'name' => 'project.track', 'display_name' => 'Theo dõi tiến độ', 'module' => 'Project', 'category' => 'Theo dõi', 'description' => 'Giám sát tiến độ thực hiện' ],
            [ 'name' => 'project.close', 'display_name' => 'Đóng dự án', 'module' => 'Project', 'category' => 'Quản lý', 'description' => 'Kết thúc và đánh giá dự án' ],
            // Đánh giá
            [ 'name' => 'evaluation.view', 'display_name' => 'Xem phiếu đánh giá', 'module' => 'Evaluation', 'category' => 'Xem', 'description' => 'Xem phiếu đánh giá nhân viên' ],
            [ 'name' => 'evaluation.create', 'display_name' => 'Tạo phiếu đánh giá', 'module' => 'Evaluation', 'category' => 'Tạo', 'description' => 'Tạo phiếu đánh giá mới' ],
            [ 'name' => 'evaluation.approve', 'display_name' => 'Duyệt phiếu đánh giá', 'module' => 'Evaluation', 'category' => 'Duyệt', 'description' => 'Duyệt phiếu đánh giá nhân viên' ],
            // Hệ thống
            [ 'name' => 'system.grant_permission', 'display_name' => 'Cấp quyền người dùng', 'module' => 'System', 'category' => 'Cấp quyền', 'description' => 'Phân quyền cho người dùng khác' ],
            [ 'name' => 'system.view_log', 'display_name' => 'Xem log hệ thống', 'module' => 'System', 'category' => 'Xem', 'description' => 'Truy cập nhật ký hệ thống' ],
            // Báo cáo
            [ 'name' => 'report.view_kpi', 'display_name' => 'Xem báo cáo KPI', 'module' => 'Report', 'category' => 'Xem', 'description' => 'Truy cập báo cáo hiệu suất' ],
            [ 'name' => 'report.export', 'display_name' => 'Xuất báo cáo', 'module' => 'Report', 'category' => 'Xuất', 'description' => 'Xuất báo cáo ra file' ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'],
                    'module' => $permission['module'],
                    'category' => $permission['category'],
                    'description' => $permission['description'],
                    'guard_name' => 'web',
                ]
            );
        }
    }
} 