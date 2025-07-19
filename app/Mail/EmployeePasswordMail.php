<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeePasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employeeName;
    public $email;
    public $password;
    public $employeeId;

    /**
     * Create a new message instance.
     */
    public function __construct($employeeName, $email, $password, $employeeId)
    {
        $this->employeeName = $employeeName;
        $this->email = $email;
        $this->password = $password;
        $this->employeeId = $employeeId;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thông tin đăng nhập tài khoản nhân viên',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.employee-password',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
} 