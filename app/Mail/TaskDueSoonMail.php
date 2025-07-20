<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskDueSoonMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $task;
    public $user;
    public $statusMsg;

    /**
     * Create a new message instance.
     */
    public function __construct($task, $user, $statusMsg)
    {
        $this->task = $task;
        $this->user = $user;
        $this->statusMsg = $statusMsg;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Nhắc nhở: Công việc "' . $this->task->content . '" sắp đến hạn')
            ->view('emails.task-due-soon');
    }
} 