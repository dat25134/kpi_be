<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        // Tạo Admin
        $admin = User::create([
            'employee_id' => 'EMP001',
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'phone' => '+84 123 456 789',
            'position' => 'director',
            'status' => 'active',
            'join_date' => '2020-01-01',
            'password' => Hash::make('admin@1234'),
        ]);
        $admin->assignRole('admin');

        // Tạo User Test
        $user = User::create([
            'employee_id' => 'EMP002',
            'name' => 'User Test',
            'email' => 'user@test.com',
            'phone' => '+84 987 654 321',
            'position' => 'employee',
            'status' => 'active',
            'join_date' => '2022-05-15',
            'password' => Hash::make('user@1234'),
        ]);
        $user->assignRole('user');

        // Tạo 30 nhân viên fake
        $positions = ['employee', 'specialist', 'manager', 'director'];
        $statuses = ['active', 'inactive'];
        $departments = range(1, 20); // Có 20 departments từ seeder

        for ($i = 3; $i <= 32; $i++) {
            $position = $faker->randomElement($positions);
            $status = $faker->randomElement($statuses);
            $departmentId = $faker->randomElement($departments);
            
            $user = User::create([
                'employee_id' => 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'position' => $position,
                'department_id' => $departmentId,
                'status' => $status,
                'join_date' => $faker->dateTimeBetween('-3 years', 'now'),
                'password' => Hash::make('password'),
            ]);

            // Gán role dựa trên position
            if ($position === 'director') {
                $user->assignRole('admin');
            } else {
                $user->assignRole('user');
            }
        }
    }
}
