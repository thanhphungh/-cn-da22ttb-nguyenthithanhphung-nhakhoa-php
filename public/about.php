<?php
session_start();

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
  <title>Giới thiệu phòng khám - Phòng khám ABC</title>
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

    .page-header {
      text-align: center;
      margin: 30px auto;
      padding: 20px;
    }
    .page-header h1 {
      font-size: 32px;
      color: #007BFF;
      margin: 0;
    }
    .content-box {
      max-width: 800px;
      margin: 30px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .content-box h2 { color: #007BFF; margin-top: 20px; }
    .content-box p, .content-box ul { line-height: 1.6; }
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

<section class="page-header">
  <h1>Giới thiệu phòng khám</h1>
</section>

<div class="content-box">
  <section class="about">
    <h2>Lịch sử hình thành</h2>
    <p>Phòng khám ABC được thành lập từ năm 2010 tại Vĩnh Long, với mục tiêu mang đến dịch vụ chăm sóc sức khỏe chất lượng cao cho cộng đồng.</p>

    <h2>Sứ mệnh</h2>
    <p>Chúng tôi cam kết cung cấp dịch vụ y tế tận tâm, an toàn và hiệu quả, đặt lợi ích của bệnh nhân lên hàng đầu.</p>

    <h2>Tầm nhìn</h2>
    <p>Trở thành phòng khám uy tín hàng đầu tại khu vực Đồng bằng sông Cửu Long, ứng dụng công nghệ hiện đại trong khám chữa bệnh.</p>

    <h2>Giá trị cốt lõi</h2>
    <ul>
      <li>Tận tâm với bệnh nhân</li>
      <li>Chất lượng dịch vụ</li>
      <li>Đội ngũ bác sĩ giàu kinh nghiệm</li>
      <li>Ứng dụng công nghệ tiên tiến</li>
    </ul>

    <h2>Cơ sở vật chất</h2>
    <p>Phòng khám được trang bị đầy đủ thiết bị y tế hiện đại, không gian thoải mái, sạch sẽ, đảm bảo sự an tâm cho bệnh nhân.</p>
  </section>
</div>

<footer>
  <p>&copy; 2025 Phòng khám ABC. All rights reserved.</p>
</footer>
</body>
</html>
