<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo các project mẫu
        $projects = [
            [
                'name' => 'Hệ thống KPI',
                'code' => 'KPI',
                'description' => 'Xây dựng hệ thống quản lý KPI cho doanh nghiệp',
                'status' => 'Đang thực hiện',
            ],
            [
                'name' => 'Ứng dụng Tây Ninh Smart',
                'code' => 'TNSMART',
                'description' => 'Ứng dụng thông minh cho tỉnh Tây Ninh',
                'status' => 'Hoàn thành',
            ],
            [
                'name' => 'Website công ty',
                'code' => 'WEBCOMP',
                'description' => 'Website chính thức của công ty',
                'status' => 'Hoàn thành',
            ],
        ];

        foreach ($projects as $data) {
            Project::updateOrCreate(['code' => $data['code']], $data);
        }

        // Gán user vào project với role và status
        $user = User::where('email', 'user@test.com')->first();
        if ($user) {
            $user->projects()->sync([
                Project::where('code', 'KPI')->first()->id => [
                    'role' => 'Project Manager',
                    'status' => 'Đang thực hiện',
                ],
                Project::where('code', 'TNSMART')->first()->id => [
                    'role' => 'Tech Lead',
                    'status' => 'Hoàn thành',
                ],
                Project::where('code', 'WEBCOMP')->first()->id => [
                    'role' => 'Technical Advisor',
                    'status' => 'Hoàn thành',
                ],
            ]);
        }
    }
} 