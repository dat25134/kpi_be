<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => Str::slug('Công việc chính'),
                'display_name' => 'Công việc chính',
                'description' => 'Các công việc chính trong tổ chức',
                'color' => 'green',
            ],
            [
                'name' => Str::slug('Công việc khác'),
                'display_name' => 'Công việc khác',
                'description' => 'Các công việc khác ngoài công việc chính',
                'color' => 'blue',
            ],
            [
                'name' => Str::slug('Dịch vụ, phát triển'),
                'display_name' => 'Dịch vụ, phát triển',
                'description' => 'Các dịch vụ và hoạt động phát triển',
                'color' => 'yellow',
            ],
            [
                'name' => Str::slug('Công việc từ eGov'),
                'display_name' => 'Công việc từ eGov',
                'description' => 'Công việc được giao từ hệ thống eGov',
                'color' => 'red',
            ],
        ];

        DB::table('categories')->insert($categories);
    }
} 