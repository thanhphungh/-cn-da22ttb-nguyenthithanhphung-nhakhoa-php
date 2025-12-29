<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (!in_array($_SESSION['role'], ['user','patient','admin'])) {
    echo "Bạn không có quyền truy cập trang này.";
    exit;
}

// Kết nối CSDL
$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Lấy danh sách bác sĩ từ bảng doctors
$stmt = $pdo->query("SELECT id, name, specialty, email, avatar FROM doctors ORDER BY created_at DESC");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đội ngũ bác sĩ - Phòng khám ABC</title>
  <link rel="stylesheet" href="style_client.css">
  <style>
    body { margin:0; padding:0; background:#f5f5f5; font-family:sans-serif; }
    .doctors { display: flex; flex-wrap: wrap; justify-content: center; margin: 30px auto; width: 90%; }
    .doctor {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin: 15px;
      padding: 20px;
      width: 250px;
      text-align: center;
    }
    .doctor img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #4CAF50;
    }
    .doctor h3 { margin: 10px 0; color: #333; }
    .doctor p { font-size: 14px; color: #555; margin: 4px 0; }
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
    <a href="dichvu.php" class="active">Dịch vụ</a>
    <a href="booking.php">Đặt lịch khám</a>
    <a href="lookup.php">Tra cứu lịch hẹn</a>
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
<section class="doctors">
  <h2 style="width:100%; text-align:center; margin-bottom:20px;">Đội ngũ bác sĩ</h2>
  <?php if (empty($doctors)): ?>
    <p style="width:100%; text-align:center;">Hiện chưa có bác sĩ nào trong hệ thống.</p>
  <?php else: ?>
    <?php foreach ($doctors as $doctor): ?>
      <div class="doctor">
        <?php
          $avatarFile = 'uploads/' . ($doctor['avatar'] ?: 'default-doctor.png');
          if (!file_exists($avatarFile)) {
              $avatarFile = 'uploads/default-doctor.png';
          }
        ?>
        <img src="<?= $avatarFile ?>" alt="Avatar bác sĩ">
        <h3><?= htmlspecialchars($doctor['name']) ?></h3>
        <p>Chuyên khoa: <?= htmlspecialchars($doctor['specialty']) ?></p>
        <p>Email: <?= htmlspecialchars($doctor['email']) ?></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<footer>
  <p>&copy; 2025 Phòng khám ABC. All rights reserved.</p>
</footer>
</body>
</html>
