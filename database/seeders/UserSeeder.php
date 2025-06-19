<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
    }
}
