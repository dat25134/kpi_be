<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            UserInfoSeeder::class,
            ProjectSeeder::class,
            ModulePermissionSeeder::class,
            PermissionSeeder::class,
            CategorySeeder::class,
            TaskSeeder::class,
        ]);
    }
}
