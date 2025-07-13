<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkDescription;
use App\Models\Evaluation;
use App\Models\Task;
use Carbon\Carbon;

class WorkDescriptionSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy các evaluation đã có
        $evaluations = Evaluation::all();

        if ($evaluations->isEmpty()) {
            $this->command->info('Không có evaluation nào để tạo work descriptions.');
            return;
        }

        foreach ($evaluations as $evaluation) {
            $userId = $evaluation->user_id;
            $monthEvaluation = $evaluation->month;
            $yearEvaluation = $evaluation->year;
            // Lấy các task liên quan đến user trong tháng đánh giá (người xử lý chính, người giao việc, người phối hợp)
            $startOfMonth = Carbon::parse($yearEvaluation . '-' . $monthEvaluation . '-01')->startOfMonth();
            $endOfMonth = Carbon::parse($yearEvaluation . '-' . $monthEvaluation . '-01')->endOfMonth();
            
            $tasks = Task::where(function($query) use ($userId, $startOfMonth, $endOfMonth) {
                $query->where(function($q) use ($userId) {
                    // Người xử lý chính
                    $q->where('main_assignee_id', $userId);
                })
                ->orWhere(function($q) use ($userId) {
                    // Người giao việc
                    $q->where('assigner_id', $userId);
                })
                ->orWhere(function($q) use ($userId) {
                    // Người tạo task
                    $q->where('created_by', $userId);
                })
                ->orWhereHas('collaborators', function($subQuery) use ($userId) {
                    // Người phối hợp
                    $subQuery->where('user_id', $userId);
                });
            })
            ->where(function($query) use ($startOfMonth, $endOfMonth) {
                $query->where(function($q) use ($startOfMonth, $endOfMonth) {
                    // Task bắt đầu trong tháng
                    $q->whereBetween('start_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                })
                ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                    // Task kết thúc trong tháng  
                    $q->whereBetween('due_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                })
                ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                    // Task đang diễn ra trong tháng (bắt đầu trước và kết thúc sau)
                    $q->where('start_date', '<', $startOfMonth->format('Y-m-d'))
                      ->where('due_date', '>', $endOfMonth->format('Y-m-d'));
                });
            })
            ->where('status', '!=', 'cancelled')
            ->get();

            if ($tasks->isEmpty()) {
                $this->command->info("Không có task nào cho user {$userId} trong tháng {$monthEvaluation}");
                continue;
            }

            $order = 1;
            foreach ($tasks as $task) {
                // Kiểm tra xem task đã có trong work_descriptions chưa
                $existingWorkDescription = WorkDescription::where('evaluation_id', $evaluation->id)
                    ->where('task_id', $task->id)
                    ->first();

                if (!$existingWorkDescription) {
                    // Tạo work_description từ task với snapshot data
                    WorkDescription::create([
                        'evaluation_id' => $evaluation->id,
                        'task_id' => $task->id,
                        
                        // Snapshot data của task tại thời điểm tạo evaluation
                        'task_title' => $task->content,
                        'task_description' => $task->description,
                        'task_status' => $task->status,
                        'task_start_date' => $task->start_date,
                        'task_due_date' => $task->due_date,
                        'task_weight' => $task->weight,
                        
                        'unit' => 'Thời gian hoàn thành',
                        'target' => 'Hoàn thành nhiệm vụ được giao',
                        'quality_weight' => 3, // Mặc định trọng số chất lượng trung bình
                        'result_level' => $this->getResultLevelFromTaskStatus($task->status),
                        'result_score' => $this->calculateResultScore($task->status, 3),
                        'final_score' => $this->calculateFinalScore($task->status, $task->weight ?? 2, 3),
                        'explanation' => $this->getExplanationFromTaskStatus($task->status),
                        'order' => $order++,
                    ]);
                }
            }

            $this->command->info("Đã tạo work descriptions cho evaluation {$evaluation->id} với {$tasks->count()} tasks");
        }

        $this->command->info('Hoàn thành tạo work descriptions từ tasks.');
    }

    /**
     * Chuyển đổi trạng thái task sang result_level
     */
    private function getResultLevelFromTaskStatus($status)
    {
        return match($status) {
            'completed' => 4, // Đạt vượt mức
            'in_progress' => 2, // Đạt, còn hạn chế
            'pending' => 1, // Không đạt
            default => 1
        };
    }

    /**
     * Tính điểm có trọng số chất lượng
     */
    private function calculateResultScore($status, $qualityWeight)
    {
        $resultLevel = $this->getResultLevelFromTaskStatus($status);
        return $resultLevel * $qualityWeight / 5;
    }

    /**
     * Tính điểm cuối cùng có tính đến độ phức tạp
     */
    private function calculateFinalScore($status, $complexityWeight, $qualityWeight)
    {
        $resultScore = $this->calculateResultScore($status, $qualityWeight);
        return $resultScore * $complexityWeight;
    }

    /**
     * Lấy diễn giải từ trạng thái task
     */
    private function getExplanationFromTaskStatus($status)
    {
        return match($status) {
            'completed' => 'Hoàn thành nhiệm vụ đúng tiến độ',
            'in_progress' => 'Đang thực hiện nhiệm vụ',
            'pending' => 'Chưa bắt đầu thực hiện',
            default => 'Trạng thái không xác định'
        };
    }
} 