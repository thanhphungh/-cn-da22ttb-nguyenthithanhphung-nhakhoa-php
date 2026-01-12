<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Lấy role nếu chưa có trong session
if (empty($_SESSION['role'])) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['role'] = $row && !empty($row['role']) ? $row['role'] : 'staff';
    } catch (Exception $e) {
        $_SESSION['role'] = 'staff';
    }
}

$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Trang quản trị</title>
  <style>
    body { margin:0; font-family:'Segoe UI', Arial, sans-serif; background:#f5f5f5; }
    header {
      background:#007BFF; color:#fff; padding:15px 20px;
      display:flex; justify-content:space-between; align-items:center;
      box-shadow:0 2px 6px rgba(0,0,0,0.2);
    }
    header h1 { margin:0; font-size:22px; }
    header .logout {
      background:#dc3545; color:#fff; padding:8px 14px;
      border-radius:6px; text-decoration:none; font-weight:bold;
    }
    header .logout:hover { background:#c82333; }

    nav { background:#333; }
    nav ul { list-style:none; margin:0; padding:10px; text-align:center; }
    nav ul li { display:inline-block; margin:0 8px; }
    nav ul li a {
      color:#fff; text-decoration:none; font-weight:500;
      padding:8px 14px; border-radius:4px; transition:background 0.3s;
    }
    nav ul li a:hover { background:#555; }
    nav ul li a.active { background:#0066cc; }

    .container { max-width:1000px; margin:30px auto; padding:0 20px; }
    .card {
      background:#fff; padding:25px; border-radius:10px;
      box-shadow:0 4px 10px rgba(0,0,0,0.1);
      text-align:center;
    }
    .card h2 { margin-top:0; color:#007BFF; }
    .card p { font-size:16px; color:#444; }
  </style>
</head>
<body>
<header>
  <h1>Trang quản trị</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>

<nav>
  <ul>
    <li><a href="dashboard.php" class="active">Trang chủ</a></li>

    <?php if ($role === 'admin'): ?>
      <li><a href="users.php">Người dùng</a></li>
      <li><a href="services.php">Dịch vụ</a></li>
      <li><a href="appointments.php">Lịch hẹn</a></li>
      <li><a href="patients.php">Quản lí bệnh nhân</a></li>
      <li><a href="posts.php">Quản lí bài đăng</a></li>
      <li><a href="invoice.php">Hóa đơn</a></li>
      <li><a href="revenue.php">Doanh thu</a></li>
      <li> <a href="quanlybacsi.php">Quản lí bác sĩ</a></li>
      <li><a href="tiepnhanlienhe.php">Tiếp nhận liên hệ</a></li>
      <li><a href="index.php">Trang khách hàng</a></li>
    <?php endif; ?>

    <?php if ($role === 'doctor'): ?>
      <li><a href="appointments.php">Lịch hẹn</a></li>
      <li><a href="medical_records.php">Hồ sơ bệnh án</a></li>
    <?php endif; ?>

    <?php if ($role === 'staff' || $role === 'receptionist'): ?>
      <li><a href="appointments.php">Lịch hẹn</a></li>
      <li><a href="patients.php">Bệnh nhân</a></li>
    <?php endif; ?>

    <?php if ($role === 'accountant'): ?>
      <li><a href="services.php">Dịch vụ</a></li>
      <li><a href="invoice.php">Hóa đơn</a></li>
    <?php endif; ?>
  </ul>
</nav>

<div class="container">
  <div class="card">
    <h2>Xin chào <?= htmlspecialchars($role) ?> 👋</h2>
    <p>Bạn đang đăng nhập với vai trò: <strong><?= htmlspecialchars($role) ?></strong></p>
    <p>Hãy chọn chức năng từ menu để bắt đầu quản lý.</p>
  </div>
</div>
</body>
</html>
