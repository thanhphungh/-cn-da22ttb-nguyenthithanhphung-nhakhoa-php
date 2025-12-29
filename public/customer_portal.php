<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cho phép cả patient và admin
if (!in_array($_SESSION['role'], ['patient','admin'])) {
    echo "Bạn không có quyền truy cập trang này.";
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Nếu là patient → lấy thông tin bệnh nhân theo email
if ($_SESSION['role']==='patient') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM patients WHERE email=?");
    $stmt->execute([$user['email']]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT a.*, d.name AS doctor_name, s.name AS service_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN services s ON a.service_id = s.id
        WHERE a.patient_id = ?
        ORDER BY a.date DESC, a.time DESC
    ");
    $stmt->execute([$patient['id']]);
    $appointments = $stmt->fetchAll();
} else {
    // Nếu là admin → hiển thị tất cả dịch vụ và lịch hẹn
    $patient = null;
    $appointments = $pdo->query("
        SELECT a.*, p.name AS patient_name, d.name AS doctor_name, s.name AS service_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN services s ON a.service_id = s.id
        ORDER BY a.date DESC, a.time DESC
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Cổng khách hàng</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>Cổng khách hàng</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="index.php">Trang chủ</a>
  <a href="customer_portal.php">Cổng khách hàng</a>
</nav>
<div class="container">
  <?php if ($_SESSION['role']==='patient' && $patient): ?>
    <div class="card">
      <h2>Thông tin cá nhân</h2>
      <p><strong>Tên:</strong> <?= htmlspecialchars($patient['name']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
      <p><strong>Điện thoại:</strong> <?= htmlspecialchars($patient['phone']) ?></p>
      <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($patient['address']) ?></p>
    </div>
  <?php else: ?>
    <div class="card">
      <h2>Admin đang xem giao diện khách hàng</h2>
      <p>Bạn có thể xem toàn bộ dịch vụ và lịch hẹn như khách hàng.</p>
    </div>
  <?php endif; ?>

  <div class="card">
    <h2>Lịch hẹn</h2>
    <table>
      <tr>
        <?php if ($_SESSION['role']==='admin'): ?><th>Bệnh nhân</th><?php endif; ?>
        <th>Ngày</th><th>Giờ</th><th>Bác sĩ</th><th>Dịch vụ</th><th>Ghi chú</th><th>Trạng thái</th>
      </tr>
      <?php foreach ($appointments as $a): ?>
        <tr>
          <?php if ($_SESSION['role']==='admin'): ?><td><?= htmlspecialchars($a['patient_name']) ?></td><?php endif; ?>
          <td><?= $a['date'] ?></td>
          <td><?= $a['time'] ?></td>
          <td><?= htmlspecialchars($a['doctor_name']) ?></td>
          <td><?= htmlspecialchars($a['service_name']) ?></td>
          <td><?= htmlspecialchars($a['note']) ?></td>
          <td><?= htmlspecialchars($a['status']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
</body>
</html>
