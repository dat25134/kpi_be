<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Role as AppRole;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles with full info
        $roles = [
            [
                'name' => 'admin',
                'code' => 'ADMIN',
                'display_name' => 'Quản trị viên',
                'description' => 'Quản trị viên hệ thống, toàn quyền',
                'order' => 0,
                'status' => 'active',
                'guard_name' => 'web',
                'color' => 'green',
            ],
            [
                'name' => 'truongphong',
                'code' => 'LDT',
                'display_name' => 'Trưởng phòng',
                'description' => 'Trưởng phòng, quản lý cấp trung',
                'order' => 1,
                'status' => 'active',
                'guard_name' => 'web',
                'color' => 'blue',
            ],
            [
                'name' => 'chuyenvien',
                'code' => 'CV',
                'display_name' => 'Chuyên viên',
                'description' => 'Chuyên viên nghiệp vụ',
                'order' => 2,
                'status' => 'active',
                'guard_name' => 'web',
                'color' => 'yellow',
            ],
            [
                'name' => 'nhanvien',
                'code' => 'NV',
                'display_name' => 'Nhân viên',
                'description' => 'Nhân viên thông thường',
                'order' => 3,
                'status' => 'active',
                'guard_name' => 'web',  
                'color' => 'red',
            ],
            [
                'name' => 'thuctapsinh',
                'code' => 'TTS',
                'display_name' => 'Thực tập sinh',
                'description' => 'Thực tập sinh',
                'order' => 4,
                'status' => 'active',
                'guard_name' => 'web',
                'color' => 'purple',
            ],
        ];

        foreach ($roles as $role) {
            AppRole::create($role);
        }

        // // Create basic permissions
        // $permissions = [
        //     // User management
        //     'view users',
        //     'create users',
        //     'edit users',
        //     'delete users',
            
        //     // Department management
        //     'view departments',
        //     'create departments',
        //     'edit departments',
        //     'delete departments',
            
        //     // Task management
        //     'view tasks',
        //     'create tasks',
        //     'edit tasks',
        //     'delete tasks',
            
        //     // Evaluation management
        //     'view evaluations',
        //     'create evaluations',
        //     'edit evaluations',
        //     'delete evaluations',
            
        //     // Role & Permission management
        //     'view roles',
        //     'create roles',
        //     'edit roles',
        //     'delete roles',
        //     'assign roles',
        // ];

        // // Create permissions and assign to admin role
        // foreach ($permissions as $permission) {
        //     Permission::create([
        //         'name' => $permission,
        //         'guard_name' => 'web'
        //     ]);
        //     $adminRole->givePermissionTo($permission);
        // }

        // // Assign basic permissions to user role
        // $userRole->givePermissionTo([
        //     'view tasks',
        //     'create tasks',
        //     'edit tasks',
        //     'view evaluations',
        //     'create evaluations',
        //     'edit evaluations',
        // ]);
    }
} 