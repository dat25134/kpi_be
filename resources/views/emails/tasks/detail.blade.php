@component('mail::message')
# Thông báo công việc mới

Xin chào {{ $user->name }},

Bạn có một công việc mới với các thông tin sau:

- **Nội dung:** {{ $task->content }}
- **Ngày bắt đầu:** {{ $task->start_date }}
- **Hạn hoàn thành:** {{ $task->due_date }}
- **Người giao việc:** {{ $task->assigner->name ?? '' }}
- **Người xử lý chính:** {{ $task->mainAssignee->name ?? '' }}
- **Phòng ban:** {{ $task->department->name ?? '' }}
- **Trọng số:** {{ $task->weight }}

@component('mail::button', ['url' => env('APP_URL_FE') . '/dashboard/'])
Xem chi tiết
@endcomponent

Cảm ơn bạn đã làm việc!
@endcomponent