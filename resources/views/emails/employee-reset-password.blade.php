<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mật khẩu đã được đặt lại</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #28a745; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #28a745; margin: 0; font-size: 24px; }
        .content { margin-bottom: 30px; }
        .info-box { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; margin: 20px 0; }
        .info-item { margin-bottom: 15px; }
        .info-label { font-weight: bold; color: #495057; display: inline-block; width: 120px; }
        .info-value { color: #28a745; font-weight: 500; }
        .password-box { background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; text-align: center; }
        .password-text { font-size: 18px; font-weight: bold; color: #856404; letter-spacing: 2px; }
        .warning { background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; padding: 15px; margin: 20px 0; color: #721c24; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 14px; }
        .btn { display: inline-block; background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Mật khẩu của bạn đã được đặt lại</h1>
        </div>
        <div class="content">
            <p>Xin chào <strong>{{ $employeeName }}</strong>,</p>
            <p>Mật khẩu đăng nhập của bạn đã được <b>đặt lại bởi quản trị viên</b>. Dưới đây là thông tin đăng nhập mới:</p>
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
                <p><strong>Mật khẩu mới:</strong></p>
                <div class="password-text">{{ $password }}</div>
            </div>
            <div class="warning">
                <p><strong>⚠️ Lưu ý quan trọng:</strong></p>
                <ul>
                    <li>Vui lòng đổi mật khẩu ngay sau khi đăng nhập lại hệ thống.</li>
                    <li>Không chia sẻ thông tin đăng nhập với người khác.</li>
                    <li>Mật khẩu này chỉ sử dụng một lần cho lần đăng nhập tiếp theo.</li>
                </ul>
            </div>
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng liên hệ ngay với bộ phận IT để được hỗ trợ.</p>
        </div>
        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống quản lý nhân viên.</p>
            <p>Nếu có thắc mắc, vui lòng liên hệ bộ phận IT.</p>
        </div>
    </div>
</body>
</html> 