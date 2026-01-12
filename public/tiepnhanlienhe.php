<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}

// Lấy danh sách liên hệ
$stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tiếp nhận liên hệ khách hàng</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .container {
      max-width:1100px;
      margin:20px auto;
    }
    .card {
      background:white;
      padding:20px;
      border-radius:8px;
      box-shadow:0 2px 6px rgba(0,0,0,0.05);
    }
    table {
      width:100%;
      border-collapse:collapse;
    }
    th, td {
      border:1px solid #ddd;
      padding:8px;
      text-align:left;
      vertical-align:top;
    }
    th {
      background:#f7f7f7;
    }
    td.message {
      white-space:pre-wrap;
      max-width:400px;
    }
  </style>
</head>
<body>
<header>
  <h1>Tiếp nhận liên hệ khách hàng</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="dashboard.php">Trang quản trị</a>
  <a href="users.php">Người dùng</a>
  <a href="services.php">Dịch vụ</a>
  <a href="appointments.php">Lịch hẹn</a>
  <a href="patients.php">Bệnh nhân</a>
  <a href="posts.php">Bài đăng</a>
  <a href="invoice.php">Hóa đơn</a>
  <a href="revenue.php">Doanh thu</a>
  <a href="quanlybacsi.php">Quản lí bác sĩ</a>
  <a href="tiepnhanlienhe.php" class="active">Tiếp nhận liên hệ</a>
  <a href="index.php">Trang khách hàng</a>
</nav>

<div class="container">
  <div class="card">
    <h2>Danh sách liên hệ từ khách hàng</h2>
    <?php if (empty($contacts)): ?>
      <p>Chưa có liên hệ nào.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Số điện thoại</th>
            <th>Nội dung</th>
            <th>Thời gian gửi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contacts as $c): ?>
            <tr>
              <td><?= $c['id'] ?></td>
              <td><?= htmlspecialchars($c['name']) ?></td>
              <td><?= htmlspecialchars($c['email']) ?></td>
              <td><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
              <td class="message"><?= nl2br(htmlspecialchars($c['message'])) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
