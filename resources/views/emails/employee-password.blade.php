<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin đăng nhập tài khoản nhân viên</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-item {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 120px;
        }
        .info-value {
            color: #007bff;
            font-weight: 500;
        }
        .password-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .password-text {
            font-size: 18px;
            font-weight: bold;
            color: #856404;
            letter-spacing: 2px;
        }
        .warning {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Chào mừng bạn đến với công ty!</h1>
        </div>

        <div class="content">
            <p>Xin chào <strong>{{ $employeeName }}</strong>,</p>
            
            <p>Tài khoản nhân viên của bạn đã được tạo thành công. Dưới đây là thông tin đăng nhập:</p>

            <div class="info-box">
                <div class="info-item">
                    <span class="info-label">Mã nhân viên:</span>
                    <span class="info-value">{{ $employeeId }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $email }}</span>
                </div>
            </div>

            <div class="password-box">
                <p><strong>Mật khẩu đăng nhập:</strong></p>
                <div class="password-text">{{ $password }}</div>
            </div>

            <div class="warning">
                <p><strong>⚠️ Lưu ý quan trọng:</strong></p>
                <ul>
                    <li>Vui lòng đổi mật khẩu ngay sau khi đăng nhập lần đầu</li>
                    <li>Không chia sẻ thông tin đăng nhập với người khác</li>
                </ul>
            </div>

            <p>Bạn có thể đăng nhập vào hệ thống bằng email và mật khẩu trên.</p>
        </div>

        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống quản lý nhân viên.</p>
            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với bộ phận IT.</p>
        </div>
    </div>
</body>
</html> 