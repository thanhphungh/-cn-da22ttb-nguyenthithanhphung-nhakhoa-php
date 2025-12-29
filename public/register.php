<?php
session_start();

try {
    $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}

$message = "";

if (isset($_POST['register'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);

    // Mã hóa mật khẩu bằng MD5 (giống các user cũ)
    $hashedPassword = md5($password);

    // xử lý avatar upload
    $avatar = null;
    if (!empty($_FILES['avatar']['name'])) {
        $target = "uploads/" . time() . "_" . basename($_FILES['avatar']['name']);
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            $avatar = $target;
        }
    }

    // kiểm tra email đã tồn tại chưa
    $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $message = "❌ Email đã tồn tại, vui lòng chọn email khác.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, avatar, role) 
                               VALUES (?, ?, ?, ?, ?, ?, 'patient')");
        $stmt->execute([$name, $email, $hashedPassword, $phone, $address, $avatar]);
        $message = "✅ Đăng ký thành công! Bạn có thể đăng nhập.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng ký tài khoản</title>
<link rel="stylesheet" href="style.css">
<style>
body { font-family: 'Segoe UI', sans-serif; background:#f4f6f9; }
.container { max-width:500px; margin:50px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
h1 { text-align:center; color:#0066cc; }
label { display:block; margin-top:10px; }
input, textarea { width:100%; padding:8px; margin-top:5px; border:1px solid #ccc; border-radius:4px; }
button { margin-top:15px; padding:10px; background:#28a745; color:#fff; border:none; border-radius:4px; cursor:pointer; }
button:hover { background:#218838; }
.message { text-align:center; margin-bottom:15px; }
</style>
</head>
<body>
<div class="container">
  <h1>Đăng ký tài khoản</h1>
  <?php if ($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <label>Họ và tên:</label>
    <input type="text" name="name" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Mật khẩu:</label>
    <input type="password" name="password" required>

    <label>Số điện thoại:</label>
    <input type="text" name="phone">

    <label>Địa chỉ:</label>
    <textarea name="address"></textarea>

    <label>Ảnh đại diện:</label>
    <input type="file" name="avatar" accept="image/*">

    <button type="submit" name="register">Đăng ký</button>
  </form>
  <p style="text-align:center;margin-top:10px;">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</div>
</body>
</html>
