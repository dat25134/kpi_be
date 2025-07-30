<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskDetailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $user;

    public function __construct($task, $user)
    {
        $this->task = $task;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Bạn có công việc mới: ' . $this->task->content)
                    ->markdown('emails.tasks.detail');
    }
}