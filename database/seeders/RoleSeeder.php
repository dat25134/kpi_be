<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Full access to all features'
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'display_name' => 'Normal User',
            'description' => 'Basic access to features'
        ]);

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