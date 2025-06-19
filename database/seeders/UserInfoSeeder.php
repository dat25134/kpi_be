<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Database\Seeder;

class UserInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Thêm thông tin cho Admin
        $admin = User::where('email', 'admin@gmail.com')->first();
        if ($admin) {
            UserInfo::create([
                'user_id' => $admin->id,
                'birth_date' => '1990-01-01',
                'avatar' => 'avatars/admin.jpg',
                'address' => 'Hà Nội, Việt Nam',
                'education' => 'Thạc sĩ Công nghệ thông tin',
                'experience' => '10 năm kinh nghiệm quản lý dự án',
                'skills' => [
                    'Project Management',
                    'Team Leadership',
                    'Strategic Planning',
                    'Risk Management',
                    'Agile Methodologies'
                ],
                'gender' => 'male',
                'salary' => 50000000
            ]);
        }

        // Thêm thông tin cho User Test
        $user = User::where('email', 'user@test.com')->first();
        if ($user) {
            UserInfo::create([
                'user_id' => $user->id,
                'birth_date' => '1995-05-15',
                'avatar' => 'avatars/user.jpg',
                'address' => 'Hồ Chí Minh, Việt Nam',
                'education' => 'Cử nhân Công nghệ thông tin',
                'experience' => '3 năm kinh nghiệm phát triển phần mềm',
                'skills' => [
                    'PHP',
                    'Laravel',
                    'Vue.js',
                    'MySQL',
                    'Git'
                ],
                'gender' => 'male',
                'salary' => 20000000
            ]);
        }
    }
} 