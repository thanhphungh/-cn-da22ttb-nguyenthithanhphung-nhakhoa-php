<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (!in_array($_SESSION['role'], ['patient','user','admin','doctor'])) {
    echo "Bạn không có quyền tra cứu lịch hẹn.";
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}

$message = null;

// ✅ Hủy lịch hẹn
if (isset($_GET['cancel'])) {
    $stmt = $pdo->prepare("UPDATE appointments SET status='Hủy' WHERE id=? AND patient_id=?");
    $stmt->execute([$_GET['cancel'], $_SESSION['user_id']]);
    $message = "Bạn đã hủy lịch hẹn thành công.";
}

// Lấy lịch hẹn của bệnh nhân hiện tại
$stmt = $pdo->prepare("SELECT a.*, 
                              s.name AS service_name, 
                              d.name AS doctor_name
                       FROM appointments a
                       LEFT JOIN services s ON a.service_id = s.id
                       LEFT JOIN doctors d ON a.doctor_id = d.id
                       WHERE a.patient_id = ?
                       ORDER BY a.date DESC, a.time DESC");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Hàm gán màu trạng thái
function getStatusColor($status) {
    return match($status) {
        'Chờ' => 'orange',
        'Đã duyệt' => 'dodgerblue',
        'Hoàn thành' => 'green',
        'Hủy' => 'red',
        default => '#333'
    };
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tra cứu lịch hẹn - Phòng khám ABC</title>
  <link rel="stylesheet" href="style_client.css">
  <style>
    table { width: 90%; margin: 30px auto; border-collapse: collapse; background: #fff; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
    th { background: #4CAF50; color: #fff; }
    tr:nth-child(even) { background: #f9f9f9; }
    .cancel-btn {
      color:#fff; background:#dc3545; padding:6px 10px; border-radius:4px; text-decoration:none;
    }
    .cancel-btn:hover { background:#c82333; }
    .msg-success {
      background:#d4edda; color:#155724; padding:10px; border-radius:6px;
      width:90%; margin:20px auto; text-align:center;
    }
    header {
      background: #007BFF;
      color: white;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .logo { font-size: 20px; font-weight: bold; }
    nav a {
      color: white;
      margin: 0 10px;
      text-decoration: none;
      font-weight: 500;
    }
    nav a:hover { text-decoration: underline; }
    .user-icon {
      display: flex;
      align-items: center;
      gap: 8px;
      position: relative;
      cursor: pointer;
    }
    .user-icon img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 2px solid white;
    }
    .dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 40px;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 6px;
      min-width: 150px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      z-index: 10;
    }
    .dropdown ul { list-style: none; margin: 0; padding: 0; }
    .dropdown li { padding: 10px; }
    .dropdown li a {
      text-decoration: none;
      color: #333;
      display: block;
    }
    .dropdown li a:hover { background: #f0f0f0; }
    .user-icon:hover .dropdown { display: block; }
    footer {
      text-align: center;
      padding: 15px;
      background: #f0f0f0;
      margin-top: 30px;
    }
  </style>
</head>
<body>
<header>
  <div class="logo">Phòng khám ABC</div>
  <nav>
    <a href="index.php">Trang chủ</a>
    <a href="about.php">Giới thiệu</a>
    <a href="dichvu.php">Dịch vụ</a>
    <a href="booking.php">Đặt lịch khám</a>
    <a href="lookup.php" class="active">Tra cứu lịch hẹn</a>
    <a href="doctors.php">Bác sĩ</a>
    <a href="contact.php">Liên hệ</a>
  </nav>
  <div class="user-icon">
    <img src="uploads/<?php echo $_SESSION['avatar'] ?? 'default-user.png'; ?>" alt="User" />
    <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
    <div class="dropdown">
      <ul>
        <li><a href="profile.php">Hồ sơ cá nhân</a></li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <li><a href="dashboard.php">Trang quản trị</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Đăng xuất</a></li>
      </ul>
    </div>
  </div>
</header>

<h2 style="text-align:center; margin-top:20px;">Lịch hẹn của bạn</h2>

<?php if ($message): ?>
  <div class="msg-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table>
  <tr>
    <th>Dịch vụ</th>
    <th>Bác sĩ</th>
    <th>Ngày</th>
    <th>Giờ</th>
    <th>Ghi chú</th>
    <th>Trạng thái</th>
    <th>Hành động</th>
  </tr>
  <?php if (empty($appointments)): ?>
    <tr><td colspan="7">Bạn chưa có lịch hẹn nào.</td></tr>
  <?php else: ?>
    <?php foreach ($appointments as $app): ?>
      <tr>
        <td><?= htmlspecialchars($app['service_name'] ?? '---') ?></td>
        <td><?= htmlspecialchars($app['doctor_name'] ?? '---') ?></td>
        <td><?= htmlspecialchars($app['date']) ?></td>
        <td><?= htmlspecialchars($app['time']) ?></td>
        <td><?= htmlspecialchars($app['note']) ?></td>
        <td style="color:<?= getStatusColor($app['status']) ?>; font-weight:bold;">
          <?= htmlspecialchars($app['status']) ?>
        </td>
        <td>
          <?php if ($app['status'] !== 'Hủy' && $app['status'] !== 'Hoàn thành'): ?>
            <a href="lookup.php?cancel=<?= $app['id'] ?>" class="cancel-btn" onclick="return confirm('Bạn có chắc muốn hủy lịch hẹn này?')">Hủy</a>
          <?php else: ?>
            ---
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
</table>

<footer>
  <p>&copy; 2025 Phòng khám ABC. All rights reserved.</p>
</footer>
</body>
</html>
