<?php
session_start();

// Kết nối CSDL
$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Lấy danh sách dịch vụ
$stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$role       = $_SESSION['role'] ?? null;
$name       = $_SESSION['name'] ?? null;
$avatar     = $_SESSION['avatar'] ?? 'default-user.png';
$loginType  = $_SESSION['login_type'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Dịch vụ - Phòng khám ABC</title>
  <link rel="stylesheet" href="style_client.css">
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background: #f5f5f5; }
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
      object-fit: cover;
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

    .services { display: flex; flex-wrap: wrap; justify-content: center; margin: 30px auto; width: 90%; }
    .service { background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin: 15px; padding: 20px; width: 280px; text-align: center; transition: transform 0.2s; }
    .service:hover { transform: scale(1.03); }
    .service img { max-width: 100%; height: 160px; object-fit: cover; border-radius: 6px; }
    .service h3 { margin: 10px 0; color: #333; }
    .service p { font-size: 14px; color: #555; }
    .price { font-weight: bold; color: #4CAF50; margin-top: 10px; }

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

  <?php if ($isLoggedIn): ?>
    <div class="user-icon">
      <?php
  $avatarSrc = 'uploads/default-user.png'; // mặc định

  if ($isLoggedIn) {
    if (($_SESSION['login_type'] ?? '') === 'google') {
      $avatarSrc = $_SESSION['avatar']; // link ảnh từ Google
    } elseif (!empty($_SESSION['avatar']) && file_exists('uploads/' . $_SESSION['avatar'])) {
      $avatarSrc = 'uploads/' . $_SESSION['avatar']; // ảnh nội bộ
    }
  }
?>
<img src="<?= htmlspecialchars($avatarSrc) ?>" alt="User" />

      <span><?= htmlspecialchars($name) ?></span>
      <div class="dropdown">
        <ul>
          <li><a href="profile.php">Hồ sơ cá nhân</a></li>
          <?php if ($role === 'admin'): ?>
            <li><a href="dashboard.php">Trang quản trị</a></li>
          <?php endif; ?>
          <li><a href="logout.php">Đăng xuất</a></li>
        </ul>
      </div>
    </div>
  <?php else: ?>
    <div>
      <a href="login.php" class="btn" style="background:#dc3545;color:#fff;padding:10px 20px;border-radius:5px;text-decoration:none;display:inline-block;">
        Đăng nhập
      </a>
      <a href="register.php" class="btn" style="background:#dc3545;color:#fff;padding:10px 20px;border-radius:5px;text-decoration:none;display:inline-block;">
        Đăng ký
      </a>
    </div>
  <?php endif; ?>
</header>

<section class="services">
  <?php foreach ($services as $service): ?>
    <div class="service">
      <?php if ($isLoggedIn): ?>
        <!-- Nếu đã đăng nhập thì cho đặt lịch -->
        <a href="booking.php?service_id=<?= $service['id'] ?>" style="text-decoration:none; color:inherit;">
      <?php else: ?>
        <!-- Nếu chưa đăng nhập thì chuyển tới login -->
        <a href="login.php" style="text-decoration:none; color:inherit;" onclick="alert('Vui lòng đăng nhập để đặt lịch!');">
      <?php endif; ?>
          <?php if (!empty($service['image'])): ?>
            <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['name']) ?>">
          <?php endif; ?>
          <h3><?= htmlspecialchars($service['name']) ?></h3>
          <p><?= nl2br(htmlspecialchars($service['description'])) ?></p>
          <div class="price"><?= number_format($service['price'], 0, ',', '.') ?> VND</div>
        </a>
    </div>
  <?php endforeach; ?>
</section>

<footer>
  <p>&copy; 2025 Phòng khám ABC. All rights reserved.</p>
</footer>
</body>
</html>
