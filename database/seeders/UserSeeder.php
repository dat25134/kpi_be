<?php

namespace Database\Seeders;

use App\Models\Role;
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
        $roleData = Role::all()->pluck('name')->toArray();
        // Tạo Admin
        $admin = User::create([
            'employee_id' => 'EMP001',
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'phone' => '+84 123 456 789',
            'status' => 'active',
            'join_date' => '2020-01-01',
            'password' => Hash::make('admin@1234'),
        ]);
        $admin->assignRole('admin');

        // // Tạo 30 nhân viên fake
        // $statuses = ['active', 'inactive'];
        // $departments = range(1, 20); // Có 20 departments từ seeder

        // for ($i = 3; $i <= 32; $i++) {
        //     $role = $faker->randomElement($roleData);
        //     $status = $faker->randomElement($statuses);
        //     $departmentId = $faker->randomElement($departments);
            
        //     $user = User::create([
        //         'employee_id' => 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT),
        //         'name' => $faker->name,
        //         'email' => $faker->unique()->safeEmail,
        //         'phone' => $faker->phoneNumber,
        //         'department_id' => $departmentId,
        //         'status' => $status,
        //         'join_date' => $faker->dateTimeBetween('-3 years', 'now'),
        //         'password' => Hash::make('password'),
        //     ]);
        //     $user->assignRole($role);
        // }
    }
}
