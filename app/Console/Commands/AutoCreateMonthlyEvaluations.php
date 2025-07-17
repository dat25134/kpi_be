<?php
namespace App\Console\Commands;

use App\Models\Evaluation;
use App\Models\EvaluationCriteria;
use App\Models\EvaluationDetail;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkDescription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCreateMonthlyEvaluations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-create-monthly-evaluations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now   = Carbon::parse('2025-07-25');
        $month = $now->month;
        $year  = $now->year;

        $users = User::where('status', 'active')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['nhanvien', 'truongphong', 'phophong', 'chuyenvien']);
            })
            ->get();

        DB::beginTransaction();
        
        foreach ($users as $user) {
            // Lấy các task user là collaborator
            $taskIdsAsCollaborator = DB::table('task_collaborators')
                ->where('user_id', $user->id)
                ->pluck('task_id')
                ->toArray();

            // Lấy công việc cần đánh giá với tất cả vai trò liên quan
            $tasks = Task::where(function ($q) use ($user, $taskIdsAsCollaborator) {
                    $q->where('main_assignee_id', $user->id)
                      ->orWhere('assigner_id', $user->id)
                      ->orWhere('created_by', $user->id);
                    if (!empty($taskIdsAsCollaborator)) {
                        $q->orWhereIn('id', $taskIdsAsCollaborator);
                    }
                })
                ->where(function ($q) use ($month, $year, $now) {
                    $q->where(function ($q2) use ($month, $year) {
                        // Đã hoàn thành trong tháng
                        $q2->where('status', 'completed')
                            ->whereMonth('completed_at', $month)
                            ->whereYear('completed_at', $year);
                    })
                    ->orWhere(function ($q2) use ($now) {
                        // Quá hạn trong tháng
                        $q2->where('status', '!=', 'completed')
                            ->where('due_date', '<', $now);
                    });
                })
                ->distinct()
                ->get();

            // Chỉ tạo phiếu đánh giá nếu có tasks cần đánh giá
            if ($tasks->count() > 0) {
                $this->info("Tạo phiếu đánh giá cho user {$user->name} - có {$tasks->count()} tasks cần đánh giá");
                
                // Tạo phiếu đánh giá
                $evaluation = Evaluation::firstOrCreate($this->getEvaluationData($user, $month, $year, $user->roles->first()->name), [
                    'department' => $user->department->name ?? null,
                    'status' => 'draft',
                ]); 

                // Bổ sung evaluation_details cho các tiêu chí hiện hành nếu chưa có
                $criteriaIds = EvaluationCriteria::where('is_active', true)
                    ->where('role_id', $user->roles->first()->id)
                    ->pluck('id')
                    ->toArray();
                    
                $existingDetailIds = EvaluationDetail::where('evaluation_id', $evaluation->id)
                    ->pluck('criteria_id')
                    ->toArray();
                    
                $missingCriteriaIds = array_diff($criteriaIds, $existingDetailIds);
                
                foreach ($missingCriteriaIds as $criteriaId) {
                    EvaluationDetail::create([
                        'evaluation_id' => $evaluation->id,
                        'criteria_id' => $criteriaId,
                    ]);
                }

                // Tạo work descriptions cho từng task
                foreach ($tasks as $task) {
                    // Tính quality_weight và result_level
                    $result_level = 1;
                    if ($task->status == 'completed' && $task->completed_at <= $task->due_date) {
                        $result_level = 3;
                    } elseif ($task->status == 'completed' && $task->completed_at > $task->due_date) {
                        $result_level = 2;
                    }
                    
                    $qualityWeight = $task->quality_weight ?? 2;

                    // Tạo work_description cho task này trong evaluation
                    WorkDescription::updateOrCreate([
                        'evaluation_id' => $evaluation->id,
                        'task_id' => $task->id,
                    ], [
                        'task_title' => $task->content,
                        'task_description' => $task->description ?? null,
                        'task_status' => $task->status,
                        'task_start_date' => $task->start_date,
                        'task_due_date' => $task->due_date,
                        'task_weight' => $task->weight,
                        'unit' => "Thời gian HT",
                        'target' => $task->due_date,
                        'quality_weight' => $qualityWeight,
                        'result_level' => $result_level,
                        'result_score' => ($result_level * $qualityWeight) / 5,
                        'final_score' => ($result_level * $qualityWeight) / 5 * $task->weight,
                    ]);
                }
            } else {
                $this->info("Bỏ qua user {$user->name} - không có tasks cần đánh giá trong tháng {$month}/{$year}");
            }
        }
        
        DB::commit();
        
        $this->info('Hoàn thành tạo phiếu đánh giá tự động cho tháng ' . $month . '/' . $year);
    }

    public function getEvaluationData($user, $month, $year, $role)
    {
        if ($role == 'nhanvien') {
            $creatorRole = 'nhanvien';
            $level1ApproverRole = 'truongphong';
            $level2ApproverRole = 'chutich';
        } elseif ($role == 'truongphong') {
            $creatorRole = 'truongphong';
            $level1ApproverRole = 'phochutich';
            $level2ApproverRole = 'chutich';
        } elseif ($role == 'phophong') {
            $creatorRole = 'phophong';
            $level1ApproverRole = 'truongphong';
            $level2ApproverRole = 'phochutich';
        } elseif ($role == 'chuyenvien') {
            $creatorRole = 'chuyenvien';
            $level1ApproverRole = 'truongphong';
            $level2ApproverRole = 'chutich';
        }
        return [
            'user_id' => $user->id,
            'month'   => $month,
            'year'    => $year,
            'creator_role' => $creatorRole,
            'level1_approver_role' => $level1ApproverRole,
            'level2_approver_role' => $level2ApproverRole,
        ];
    }
}
