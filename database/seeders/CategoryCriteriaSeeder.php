<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('category_criteria')->insert([
            [
                'name' => 'Chính trị, tư tưởng',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Đạo đức, lối sống',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tác phong, lề lối làm việc',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ý thức tổ chức kỷ luật',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Thực hiện chuyển đổi số và cải cách hành chính',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Năng lực lãnh đạo, quản lý',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kết quả thực hiện nhiệm vụ, chức trách được giao',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
} 