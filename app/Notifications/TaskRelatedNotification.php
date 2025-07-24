<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\Channel;

class TaskRelatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database']; // KHÔNG có 'broadcast' để không broadcast mặc định
    }

    public function toArray($notifiable)
    {
        return [
            'id' => $this->data['id'] ?? null,
            'title' => $this->data['title'] ?? '',
            'content' => $this->data['content'] ?? '',
            'url' => $this->data['url'] ?? '',
            'read_at' => null,
            'created_at' => now(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastType()
    {
        return 'notification';
    }

    public function broadcastOn()
    {
        // Dùng public channel, cần truyền user_id vào $data khi notify
        return [new Channel('user.' . $this->data['user_id'])];
    }
} 