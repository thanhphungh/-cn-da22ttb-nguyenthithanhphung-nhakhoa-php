<?php
session_start();

$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$role       = $_SESSION['role'] ?? null;
$name       = $_SESSION['name'] ?? null;
$avatar     = $_SESSION['avatar'] ?? 'default-user.png';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Trang chủ - Phòng khám ABC</title>
  <link rel="stylesheet" href="style_client.css">
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
<section class="banner">
  <h1>Chăm sóc sức khỏe tận tâm – Vì bạn và gia đình</h1>
  <a href="booking.php" class="btn">Đặt lịch ngay</a>
</section>

<section class="posts">
  <h2>Bài viết mới nhất</h2>
  <?php if (empty($posts)): ?>
    <p>Hiện chưa có bài viết nào.</p>
  <?php else: ?>
    <?php foreach ($posts as $post): ?>
      <div class="post">
        <h2><?= htmlspecialchars($post['title']) ?></h2>
        <p class="date">Đăng ngày: <?= htmlspecialchars($post['created_at']) ?></p>
        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
        <?php if (!empty($post['image'])): ?>
          <img src="uploads/<?= htmlspecialchars($post['image']) ?>" alt="Ảnh bài đăng">
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<footer>
  <p>&copy; 2025 Phòng khám ABC. All rights reserved.</p>
</footer>
</body>
</html>
