<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ModulePermission;

class ModulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'HR',
                'display_name' => 'Quản lý Nhân sự',
                'icon' => 'Users',
                'color' => 'bg-blue-100 text-blue-800',
                'description' => 'Quản lý nhân viên, chức vụ, hồ sơ nhân sự',
            ],
            [
                'name' => 'Department',
                'display_name' => 'Phòng ban',
                'icon' => 'Building2',
                'color' => 'bg-cyan-100 text-cyan-800',
                'description' => 'Quản lý các phòng ban trong công ty',
            ],
            [
                'name' => 'Project',
                'display_name' => 'Quản lý Dự án/Công việc',
                'icon' => 'FileText',
                'color' => 'bg-purple-100 text-purple-800',
                'description' => 'Quản lý dự án, công việc, tiến độ',
            ],
            [
                'name' => 'Evaluation',
                'display_name' => 'Đánh giá',
                'icon' => 'ClipboardCheck',
                'color' => 'bg-pink-100 text-pink-800',
                'description' => 'Đánh giá nhân viên, phiếu đánh giá',
            ],
            [
                'name' => 'System',
                'display_name' => 'Hệ thống',
                'icon' => 'Settings',
                'color' => 'bg-red-100 text-red-800',
                'description' => 'Quản trị hệ thống, phân quyền',
            ],
            [
                'name' => 'Report',
                'display_name' => 'Báo cáo',
                'icon' => 'FileText',
                'color' => 'bg-orange-100 text-orange-800',
                'description' => 'Báo cáo hiệu suất, KPI',
            ],
        ];

        foreach ($modules as $module) {
            ModulePermission::updateOrCreate(
                ['name' => $module['name']],
                $module
            );
        }
    }
} 