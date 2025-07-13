<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $users = User::all();

        if ($categories->count() == 0 || $users->count() < 4) {
            // Đảm bảo có đủ dữ liệu để seed
            return;
        }

        $numTasks = 50; // Số lượng task để test phân trang

        $taskIds = [];
        for ($i = 1; $i <= $numTasks; $i++) {
            // Random assigner, main assignee, collaborators
            $assigner = $users->random();
            $mainAssignee = $users->where('id', '!=', $assigner->id)->random();
            $collaborators = $users->whereNotIn('id', [$assigner->id, $mainAssignee->id])->random(2)->pluck('id')->toArray();
            $category = $categories->random();

            // 70% task là gốc, 30% là subtask
            $parentId = null;
            if ($i > 1 && rand(1, 100) <= 30) {
                // Chọn ngẫu nhiên 1 task đã tạo trước đó làm cha
                $parentId = collect($taskIds)->random();
            }

            $task = Task::create([
                'content' => "Task $i: Nội dung công việc mẫu để test phân trang.",
                'start_date' => now()->subDays($i)->toDateString(),
                'due_date' => now()->addDays($i + 7)->toDateString(),
                'category_id' => $category->id,
                'department_id' => $mainAssignee->department_id, // Gán phòng ban theo người chịu trách nhiệm chính
                'weight' => rand(1, 5),
                'assigner_id' => $assigner->id,
                'main_assignee_id' => $mainAssignee->id,
                'status' => collect(['pending', 'in_progress', 'completed', 'cancelled'])->random(),
                'created_by' => $assigner->id,
                'parent_id' => $parentId,
            ]);

            if ($task->status == 'completed') {
                $task->completed_at = now();
                $task->save();
            }

            $taskIds[] = $task->id;

            foreach ($collaborators as $userId) {
                DB::table('task_collaborators')->insert([
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
} 