@php
    use Illuminate\Support\Carbon;
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhắc nhở công việc sắp đến hạn</title>
</head>
<body>
    <h2>Nhắc nhở công việc sắp đến hạn</h2>
    <p>Xin chào {{ $user->name }},</p>
    <p>Bạn có một công việc đang được tiến hành với thông tin như sau:</p>
    <ul>
        <li><strong>Tên công việc:</strong> {{ $task->name ?? $task->content }}</li>
        <li><strong>Hạn xử lý:</strong> {{ $task->due_date ? Carbon::parse($task->due_date)->format('d/m/Y') : 'Chưa xác định' }}</li>
        <li><strong>Trạng thái:</strong> {{ $statusMsg }}</li>
        @if($task->description ?? false)
        <li><strong>Mô tả:</strong> {{ $task->description }}</li>
        @endif
    </ul>
    @if(isset($task->id))
    <p><a href="{{ env('APP_URL_FE') }}/dashboard">Xem danh sách công việc</a></p>
    @endif
    <p>Vui lòng kiểm tra và hoàn thành công việc đúng hạn!</p>
    <hr>
    <p style="font-size:12px;color:#888;">Email này được gửi tự động từ hệ thống quản lý công việc.</p>
</body>
</html> 