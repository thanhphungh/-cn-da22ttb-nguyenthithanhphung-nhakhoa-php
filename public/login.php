<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    // Truy vấn user theo name
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");
    $stmt->execute([$name]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra mật khẩu (MD5)
    if ($user && md5($password) === $user['password']) {
        // Lưu thông tin vào session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['name'];

        // Phân quyền chuyển hướng
        if ($user['role'] === 'patient' || $user['role'] === 'user') {
            header("Location: index.php"); // Trang chủ cho bệnh nhân
        } else {
            header("Location: dashboard.php"); // Trang quản trị
        }
        exit;
    } else {
        $error = "Sai tên đăng nhập hoặc mật khẩu!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập hệ thống</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .login-box {
      background: #fff;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      width: 350px;
      text-align: center;
    }
    .login-box h2 {
      margin-bottom: 20px;
      color: #333;
    }
    .login-box input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }
    .login-box button {
      width: 100%;
      padding: 12px;
      background: #4CAF50;
      border: none;
      border-radius: 6px;
      color: #fff;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }
    .login-box button:hover {
      background: #45a049;
    }
    .error {
      color: red;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="login-box">
  <h2>Đăng nhập hệ thống</h2>
  <form method="post">
    <input type="text" name="name" placeholder="Tên đăng nhập" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <button type="submit">Đăng nhập</button>
  </form>
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

  <!-- Đường dẫn sang trang đăng ký -->
  <p style="margin-top:15px;">
    Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
  </p>
</div>
</body>
</html>
