<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Mail\TaskDueSoonMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class RemindTasksDueSoon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:remind-due-soon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi email nhắc nhở các công việc còn 3 ngày nữa đến hạn cho user liên quan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $maxDate = $today->copy()->addDays(3);
        $tasks = Task::where('status', 'in_progress')
            ->whereDate('due_date', '<=', $maxDate)
            ->with(['assigner', 'mainAssignee', 'collaborators'])
            ->get();
        $count = 0;
        foreach ($tasks as $task) {
            $users = collect();
            if ($task->assigner) $users->push($task->assigner);
            if ($task->mainAssignee) $users->push($task->mainAssignee);
            if ($task->collaborators && $task->collaborators->count()) {
                foreach ($task->collaborators as $user) {
                    $users->push($user);
                }
            }
            $uniqueUsers = $users->unique('id');
            foreach ($uniqueUsers as $user) {
                $dueDate = Carbon::parse($task->due_date);
                if ($dueDate->lt($today)) {
                    $statusMsg = 'Công việc đã quá hạn!';
                } else {
                    $daysLeft = $today->diffInDays($dueDate);
                    $statusMsg = "Còn $daysLeft ngày nữa đến hạn xử lý.";
                }
                Mail::to($user->email)->queue(new \App\Mail\TaskDueSoonMail($task, $user, $statusMsg));
                $count++;
            }
        }
        $this->info("Đã queue gửi nhắc nhở cho $count người dùng.");
    }
} 