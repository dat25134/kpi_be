<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Phòng Quản trị nền tảng số và VTTT',
                'code' => 'QTNT',
                'description' => 'Phụ trách quản trị hệ thống thông tin, phát triển nền tảng số và vận hành truyền thông.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Kỹ thuật',
                'code' => 'KT',
                'description' => 'Phụ trách kỹ thuật, bảo trì hệ thống và hỗ trợ kỹ thuật.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Nhân sự',
                'code' => 'NS',
                'description' => 'Quản lý nhân sự, tuyển dụng và đào tạo.',
                'status' => 'active',
            ],
        ];

        foreach ($departments as $data) {
            Department::updateOrCreate(['code' => $data['code']], $data);
        }

        // Gán trưởng phòng cho phòng ban mẫu (nếu có user phù hợp)
        $manager = User::where('email', 'admin@gmail.com')->first();
        if ($manager) {
            $qtnt = Department::where('code', 'QTNT')->first();
            if ($qtnt) {
                $qtnt->manager_id = $manager->id;
                $qtnt->save();
            }
        }
    }
} 