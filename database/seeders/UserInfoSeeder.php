<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

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

        // // Thêm thông tin cho 30 nhân viên fake
        // $users = User::whereNotIn('email', ['admin@gmail.com', 'user@test.com'])->get();
        
        // $educations = [
        //     'Cử nhân Công nghệ thông tin',
        //     'Cử nhân Kinh tế',
        //     'Cử nhân Marketing',
        //     'Thạc sĩ Quản trị kinh doanh',
        //     'Cử nhân Tài chính - Ngân hàng',
        //     'Cử nhân Kế toán',
        //     'Cử nhân Luật',
        //     'Thạc sĩ Công nghệ thông tin'
        // ];

        // $skillSets = [
        //     ['PHP', 'Laravel', 'MySQL', 'Git', 'Docker'],
        //     ['JavaScript', 'Vue.js', 'React', 'Node.js', 'MongoDB'],
        //     ['Python', 'Django', 'PostgreSQL', 'AWS', 'Docker'],
        //     ['Java', 'Spring Boot', 'Oracle', 'Maven', 'Jenkins'],
        //     ['C#', '.NET', 'SQL Server', 'Azure', 'Visual Studio'],
        //     ['Marketing', 'SEO', 'Google Analytics', 'Social Media', 'Content Creation'],
        //     ['Sales', 'Customer Relationship', 'Negotiation', 'Market Research', 'CRM'],
        //     ['Finance', 'Excel', 'Financial Analysis', 'Budgeting', 'Risk Management'],
        //     ['HR', 'Recruitment', 'Employee Relations', 'Performance Management', 'HRIS'],
        //     ['Design', 'Photoshop', 'Illustrator', 'Figma', 'UI/UX Design']
        // ];

        // foreach ($users as $user) {
        //     $skills = $faker->randomElement($skillSets);
        //     $salary = $faker->numberBetween(15000000, 80000000);
            
        //     UserInfo::create([
        //         'user_id' => $user->id,
        //         'birth_date' => $faker->dateTimeBetween('-50 years', '-20 years'),
        //         'avatar' => 'avatars/default.jpg',
        //         'address' => $faker->address,
        //         'education' => $faker->randomElement($educations),
        //         'experience' => $faker->numberBetween(1, 15) . ' năm kinh nghiệm',
        //         'skills' => $skills,
        //         'gender' => $faker->randomElement(['male', 'female', 'other']),
        //         'salary' => $salary
        //     ]);
        // }
    }
} 