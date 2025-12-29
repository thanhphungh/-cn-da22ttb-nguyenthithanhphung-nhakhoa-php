<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (!in_array($_SESSION['role'], ['patient','admin'])) {
    echo "Bạn không có quyền truy cập trang này.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Giới thiệu phòng khám - Phòng khám ABC</title>
  <!-- CSS riêng cho Client Site -->
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

    .banner {
      text-align: center;
      padding: 50px 20px;
      background: #eafbea;
    }
    .banner h1 {
      font-size: 28px;
      margin-bottom: 20px;
    }
    .banner .btn {
      background: #007BFF;
      color: white;
      padding: 12px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
    }
    .banner .btn:hover { background: #0056b3; }

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
    .page-header {
  text-align: center;      /* căn giữa nội dung theo chiều ngang */
  margin: 30px auto;       /* tạo khoảng cách trên dưới và căn giữa khung */
  padding: 20px;           /* thêm khoảng cách bên trong */
}

.page-header h1 {
  font-size: 32px;
  color: #007BFF;
  margin: 0;
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
