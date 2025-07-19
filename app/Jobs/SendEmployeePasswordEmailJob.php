<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\EmployeePasswordMail;

class SendEmployeePasswordEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $employeeName;
    public $email;
    public $password;
    public $employeeId;
    public $tries = 3; // Số lần thử lại nếu thất bại
    public $timeout = 30; // Timeout 30 giây

    /**
     * Create a new job instance.
     */
    public function __construct($employeeName, $email, $password, $employeeId)
    {
        $this->employeeName = $employeeName;
        $this->email = $email;
        $this->password = $password;
        $this->employeeId = $employeeId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->email)->send(new EmployeePasswordMail(
                $this->employeeName,
                $this->email,
                $this->password,
                $this->employeeId
            ));

            Log::info('Email mật khẩu đã được gửi thành công', [
                'email' => $this->email,
                'employee_id' => $this->employeeId
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi gửi email mật khẩu cho nhân viên', [
                'email' => $this->email,
                'employee_id' => $this->employeeId,
                'error' => $e->getMessage()
            ]);

            // Ném lại exception để job có thể retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job gửi email mật khẩu thất bại hoàn toàn', [
            'email' => $this->email,
            'employee_id' => $this->employeeId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
} 