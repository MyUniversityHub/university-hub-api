<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin tài khoản</title>
</head>
<body>
<h2>Chào {{ $user->name }},</h2>
<p>Tài khoản của bạn đã được tạo thành công. Dưới đây là thông tin đăng nhập:</p>
<p><strong>Tên đăng nhập:</strong> {{ $user->user_name }}</p>
<p><strong>Mật khẩu:</strong> {{ $password }}</p>
<p>Vui lòng đăng nhập và đổi mật khẩu ngay để bảo mật tài khoản.</p>
<p>Trân trọng,<br>Hệ thống quản lý</p>
</body>
</html>
