<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\TaskProgress;
use App\Models\User;
use Carbon\Carbon;

class TaskProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách tasks và users có sẵn
        $tasks = Task::all();
        $users = User::all();

        if ($tasks->isEmpty() || $users->isEmpty()) {
            $this->command->info('Không có tasks hoặc users để tạo progress. Vui lòng chạy TaskSeeder và UserSeeder trước.');
            return;
        }

        $progressData = [
            [
                'content' => 'Bắt đầu thực hiện công việc theo kế hoạch',
                'days_ago' => 5,
            ],
            [
                'content' => 'Đã hoàn thành 50% công việc, đang chờ phản hồi từ khách hàng',
                'days_ago' => 3,
            ],
            [
                'content' => 'Nhận được phản hồi từ KH, cần điều chỉnh một số điểm',
                'days_ago' => 2,
            ],
            [
                'content' => 'Đã điều chỉnh theo yêu cầu, gửi lại cho KH review',
                'days_ago' => 1,
            ],
            [
                'content' => 'KH đã duyệt, chuẩn bị bàn giao',
                'days_ago' => 0,
            ],
            [
                'content' => 'Chờ KH của Sở ban hành, căn cứ thực hiện',
                'days_ago' => 4,
            ],
            [
                'content' => 'Chuyển công việc cho Mỹ Khánh thực hiện dự thảo',
                'days_ago' => 2,
            ],
            [
                'content' => 'Đã hoàn thành dự thảo, chờ phê duyệt',
                'days_ago' => 1,
            ],
            [
                'content' => 'Cần bổ sung thêm tài liệu hỗ trợ',
                'days_ago' => 0,
            ],
            [
                'content' => 'Đã bổ sung đầy đủ tài liệu, gửi lên cấp trên',
                'days_ago' => 0,
            ],
        ];

        $createdCount = 0;

        foreach ($tasks as $task) {
            // Tạo 2-5 progress entries cho mỗi task
            $numProgress = rand(2, 5);
            $selectedProgress = array_rand($progressData, $numProgress);
            
            if (!is_array($selectedProgress)) {
                $selectedProgress = [$selectedProgress];
            }

            foreach ($selectedProgress as $index) {
                $progressInfo = $progressData[$index];
                $randomUser = $users->random();
                
                TaskProgress::create([
                    'task_id' => $task->id,
                    'user_id' => $randomUser->id,
                    'content' => $progressInfo['content'],
                    'created_at' => Carbon::now()->subDays($progressInfo['days_ago'] + rand(0, 2)),
                    'updated_at' => Carbon::now()->subDays($progressInfo['days_ago'] + rand(0, 2)),
                ]);
                
                $createdCount++;
            }
        }

        $this->command->info("Đã tạo {$createdCount} progress entries cho {$tasks->count()} tasks.");
    }
} 