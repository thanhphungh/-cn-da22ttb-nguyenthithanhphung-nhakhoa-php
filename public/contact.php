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

$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $content = trim($_POST['message'] ?? '');
    if ($name && $email && $phone && $content) {
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $content]);
        $message = "Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm.";
    } else {
        $message = "Vui lòng nhập đầy đủ thông tin.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Liên hệ - Phòng khám ABC</title>
  <style>
   body {
  margin:0;
  font-family: 'Segoe UI', Arial, sans-serif;
  background:#eef2f7;
  color:#333;
}

header {
  background:#007BFF;
  color:#fff;
  padding:12px 20px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  box-shadow:0 2px 6px rgba(0,0,0,0.2);
}

.logo {
  font-size:22px;
  font-weight:bold;
}

nav a {
  color:#fff;
  margin:0 12px;
  text-decoration:none;
  font-weight:500;
  transition:color 0.3s;
}
nav a:hover {
  color:#ffc107;
}

.user-icon {
  display:flex;
  align-items:center;
  gap:8px;
  position:relative;
  cursor:pointer;
}
.user-icon img {
  width:36px;
  height:36px;
  border-radius:50%;
  border:2px solid #fff;
}
.dropdown {
  display:none;
  position:absolute;
  right:0;
  top:48px;
  background:#fff;
  border:1px solid #ccc;
  border-radius:6px;
  min-width:160px;
  box-shadow:0 4px 8px rgba(0,0,0,0.15);
}
.user-icon:hover .dropdown { display:block; }
.dropdown a {
  display:block;
  padding:10px;
  color:#333;
  text-decoration:none;
}
.dropdown a:hover {
  background:#f0f0f0;
}

.contact-box {
  max-width:600px;
  margin:60px auto;
  background:#fff;
  padding:30px;
  border-radius:12px;
  box-shadow:0 6px 12px rgba(0,0,0,0.1);
}
.contact-box h2 {
  text-align:center;
  margin-bottom:25px;
  color:#007BFF;
  font-size:24px;
}
.contact-box label {
  display:block;
  margin-top:15px;
  font-weight:600;
  color:#444;
}
.contact-box input,
.contact-box textarea {
  width:100%;
  padding:12px;
  margin-top:6px;
  border:1px solid #ccc;
  border-radius:6px;
  font-size:15px;
  transition:border-color 0.3s, box-shadow 0.3s;
}
.contact-box input:focus,
.contact-box textarea:focus {
  border-color:#007BFF;
  box-shadow:0 0 6px rgba(0,123,255,0.3);
  outline:none;
}
.contact-box button {
  margin-top:20px;
  width:100%;
  padding:14px;
  background:#28a745;
  color:#fff;
  border:none;
  border-radius:6px;
  font-size:16px;
  font-weight:bold;
  cursor:pointer;
  transition:background 0.3s;
}
.contact-box button:hover {
  background:#218838;
}
.message {
  text-align:center;
  color:#155724;
  background:#d4edda;
  padding:12px;
  border-radius:6px;
  margin-bottom:15px;
  font-weight:bold;
}

footer {
  text-align:center;
  padding:15px;
  background:#f0f0f0;
  margin-top:40px;
  font-size:14px;
  color:#555;
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
<div class="contact-box">
  <h2>Liên hệ với chúng tôi</h2>
  <?php if ($message): ?><p class="message"><?= htmlspecialchars($message) ?></p><?php endif; ?>
  <form method="post">
    <label>Họ tên:</label>
    <input type="text" name="name" required>
    <label>Email:</label>
    <input type="email" name="email" required>
    <label>Số điện thoại:</label>
    <input type="text" name="phone" required>
    <label>Nội dung:</label>
    <textarea name="message" rows="4" required></textarea>
    <button type="submit">Gửi liên hệ</button>
  </form>
</div>

<footer>
  <p>&copy; 2025 Phòng khám ABC. All rights reserved.</p>
</footer>
</body>
</html>
