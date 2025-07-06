<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Evaluation;
use App\Models\EvaluationDetail;
use App\Models\User;
use App\Models\Department;
use App\Models\EvaluationCriteria;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EvaluationSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tháng và năm hiện tại
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $period = Carbon::now()->format('Y-m');

        // Lấy các user có tasks liên quan trong tháng hiện tại (người xử lý chính, người giao việc, người phối hợp)
        $startOfMonth = Carbon::parse($period . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($period . '-01')->endOfMonth();
        
        $usersWithTasks = User::with('roles')
            ->where(function($query) use ($startOfMonth, $endOfMonth) {
                // Người xử lý chính
                $query->whereHas('assignedTasks', function($subQuery) use ($startOfMonth, $endOfMonth) {
                    $subQuery->where(function($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereBetween('start_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                    })
                    ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereBetween('due_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                    })
                    ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('start_date', '<', $startOfMonth->format('Y-m-d'))
                          ->where('due_date', '>', $endOfMonth->format('Y-m-d'));
                    });
                });
            })
            ->orWhere(function($query) use ($startOfMonth, $endOfMonth) {
                // Người giao việc
                $query->whereHas('assignedTasks', function($subQuery) use ($startOfMonth, $endOfMonth) {
                    $subQuery->where('assigner_id', DB::raw('users.id'))
                        ->where(function($q) use ($startOfMonth, $endOfMonth) {
                            $q->whereBetween('start_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                        })
                        ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                            $q->whereBetween('due_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                        })
                        ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                            $q->where('start_date', '<', $startOfMonth->format('Y-m-d'))
                              ->where('due_date', '>', $endOfMonth->format('Y-m-d'));
                        });
                });
            })
            ->orWhere(function($query) use ($startOfMonth, $endOfMonth) {
                // Người tạo task
                $query->whereHas('assignedTasks', function($subQuery) use ($startOfMonth, $endOfMonth) {
                    $subQuery->where('created_by', DB::raw('users.id'))
                        ->where(function($q) use ($startOfMonth, $endOfMonth) {
                            $q->whereBetween('start_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                        })
                        ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                            $q->whereBetween('due_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                        })
                        ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                            $q->where('start_date', '<', $startOfMonth->format('Y-m-d'))
                              ->where('due_date', '>', $endOfMonth->format('Y-m-d'));
                        });
                });
            })
            ->orWhere(function($query) use ($startOfMonth, $endOfMonth) {
                // Người phối hợp
                $query->whereHas('assignedTasks', function($subQuery) use ($startOfMonth, $endOfMonth) {
                    $subQuery->whereHas('collaborators', function($collabQuery) {
                        $collabQuery->where('user_id', DB::raw('users.id'));
                    })
                    ->where(function($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereBetween('start_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                    })
                    ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereBetween('due_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                    })
                    ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('start_date', '<', $startOfMonth->format('Y-m-d'))
                          ->where('due_date', '>', $endOfMonth->format('Y-m-d'));
                    });
                });
            })
            ->whereHas('assignedTasks', function($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->distinct()
            ->get();

        if ($usersWithTasks->isEmpty()) {
            $this->command->info('Không có user nào có tasks trong tháng hiện tại.');
            return;
        }

        $this->command->info("Tìm thấy {$usersWithTasks->count()} user có tasks trong tháng {$period}");

        $departments = Department::take(3)->get();

        foreach ($usersWithTasks as $user) {
            $userRole = $user->roles->first();
            if (!$userRole) {
                $this->command->info("User {$user->id} không có role, bỏ qua.");
                continue;
            }

            // Map role name sang role_type cho evaluation
            $roleMapping = [
                'truongphong' => 'truongphong',
                'phophong' => 'phophong', 
                'nhanvien' => 'nhanvien',
            ];

            $roleType = $roleMapping[$userRole->name] ?? null;
            if (!$roleType) {
                $this->command->info("User {$user->id} có role không hợp lệ: {$userRole->name}");
                continue;
            }

            // Kiểm tra xem đã có evaluation cho user này trong tháng này chưa
            $existingEvaluation = Evaluation::where('user_id', $user->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();

            if ($existingEvaluation) {
                $this->command->info("User {$user->id} đã có evaluation trong tháng {$period}, bỏ qua.");
                continue;
            }

            // Tạo evaluation cho user này
            $evaluation = Evaluation::create([
                'user_id' => $user->id,
                'department_id' => $user->department_id ?? $departments->random()->id,
                'month' => $currentMonth,
                'year' => $currentYear,
                'total_score' => 0, // Sẽ được tính sau
                'final_grade' => null, // Sẽ được tính sau
                'status' => 'draft',
            ]);

            // Lấy các tiêu chí đánh giá cho role_type này
            $criteria = EvaluationCriteria::where('role_type', $roleType)
                ->where('is_active', true)
                ->orderBy('order')
                ->get();

            $totalScore = 0;

            foreach ($criteria as $criterion) {
                // Tạo điểm đánh giá mẫu (tự đánh giá)
                $selfScore = $this->generateRandomScore($criterion->max_score);
                $totalScore += $selfScore;

                EvaluationDetail::create([
                    'evaluation_id' => $evaluation->id,
                    'criteria_id' => $criterion->id,
                    'self_score' => $selfScore,
                    'self_comment' => $this->generateSampleComment($criterion->name, $selfScore, $criterion->max_score),
                    'level1_score' => null, // Chưa có đánh giá cấp 1
                    'level1_comment' => null,
                    'level2_score' => null, // Chưa có đánh giá cấp 2
                    'level2_comment' => null,
                    'final_score' => $selfScore, // Tạm thời lấy điểm tự đánh giá
                ]);
            }

            // Cập nhật tổng điểm và xếp loại
            $evaluation->update([
                'total_score' => $totalScore,
                'final_grade' => $this->calculateGrade($totalScore)
            ]);

            $this->command->info("Đã tạo evaluation cho user {$user->id} ({$user->name}) với tổng điểm: {$totalScore}");
        }

        $this->command->info('Hoàn thành tạo evaluations cho các user có tasks trong tháng.');
    }

    /**
     * Tạo điểm số ngẫu nhiên dựa trên điểm tối đa
     */
    private function generateRandomScore($maxScore)
    {
        // Tạo điểm từ 70% đến 95% của điểm tối đa
        $percentage = rand(70, 95) / 100;
        return round($maxScore * $percentage, 2);
    }

    /**
     * Tạo nhận xét mẫu
     */
    private function generateSampleComment($criteriaName, $score, $maxScore)
    {
        $percentage = ($score / $maxScore) * 100;
        
        if ($percentage >= 90) {
            return "Hoàn thành xuất sắc tiêu chí {$criteriaName}. Thực hiện tốt các yêu cầu đề ra.";
        } elseif ($percentage >= 80) {
            return "Hoàn thành tốt tiêu chí {$criteriaName}. Có một số điểm cần cải thiện.";
        } elseif ($percentage >= 70) {
            return "Hoàn thành tiêu chí {$criteriaName}. Cần nỗ lực thêm để đạt kết quả tốt hơn.";
        } else {
            return "Cần cải thiện tiêu chí {$criteriaName}. Có nhiều điểm cần khắc phục.";
        }
    }

    /**
     * Tính xếp loại dựa trên tổng điểm
     */
    private function calculateGrade($totalScore)
    {
        if ($totalScore >= 90) {
            return 'A';
        } elseif ($totalScore >= 70) {
            return 'B';
        } elseif ($totalScore >= 50) {
            return 'C';
        } else {
            return 'D';
        }
    }
} 