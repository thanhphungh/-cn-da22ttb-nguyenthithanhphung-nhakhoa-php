<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$user_id = $_SESSION['user_id'];
$message = "";
$showForm = false;

// Lấy thông tin người dùng
$stmt = $pdo->prepare("SELECT id, name, email, role, avatar, address, phone FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu nhấn nút chỉnh sửa
if (isset($_POST['edit'])) {
    $showForm = true;
}

// Nếu cập nhật thông tin
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // xử lý avatar upload
    $avatar = $user['avatar'];
    if (!empty($_FILES['avatar']['name'])) {
        $target = "uploads/" . time() . "_" . basename($_FILES['avatar']['name']);
        move_uploaded_file($_FILES['avatar']['tmp_name'], $target);
        $avatar = $target;
    }

    if ($password) {
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, avatar=?, address=?, phone=? WHERE id=?");
        $stmt->execute([$name, $email, $password, $avatar, $address, $phone, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, avatar=?, address=?, phone=? WHERE id=?");
        $stmt->execute([$name, $email, $avatar, $address, $phone, $user_id]);
    }

    $message = "✅ Đã cập nhật thông tin cá nhân.";
    // refresh lại dữ liệu
    $stmt = $pdo->prepare("SELECT id, name, email, role, avatar, address, phone FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $showForm = false; // sau khi cập nhật thì ẩn form
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Hồ sơ cá nhân</title>
<link rel="stylesheet" href="style_client.css">
<style>
.container { width:60%; margin:30px auto; }
.card { background:#fff; padding:20px; border:1px solid #ccc; border-radius:8px; }
img.avatar { max-width:120px; border-radius:50%; margin-bottom:10px; }
label { display:block; margin-top:10px; }
input, textarea { width:100%; padding:8px; }
button { margin-top:15px; padding:10px 15px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer; }
button:hover { background:#0056b3; }
.message { background:#e0ffe0; padding:10px; margin-bottom:15px; border:1px solid #0c0; }
.topbar {
  background:#007BFF; color:#fff; padding:12px 20px;
  display:flex; justify-content:space-between; align-items:center;
}
.topbar .logo { font-size:20px; font-weight:bold; }
.topbar nav a { color:#fff; margin:0 10px; text-decoration:none; font-weight:500; }
.topbar nav a:hover { color:#ffc107; }
.actions { display:flex; gap:10px; }
.btn-back, .btn-logout {
  padding:8px 14px; border:none; border-radius:6px; cursor:pointer; font-weight:500; text-decoration:none;
}
.btn-back { background:#6c757d; color:#fff; }
.btn-back:hover { background:#5a6268; }
.btn-logout { background:#dc3545; color:#fff; }
.btn-logout:hover { background:#c82333; }
</style>
</head>
<body>
<header class="topbar">
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
  <div class="actions">
    <a href="logout.php" class="btn-logout">Đăng xuất</a>
  </div>
</header>
<div class="container">
  <?php if ($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>

  <div class="card">
    <h2>Thông tin của bạn</h2>
    <?php if ($user['avatar']): ?>
      <img src="<?= htmlspecialchars($user['avatar']) ?>" class="avatar" alt="Avatar">
    <?php endif; ?>
    <p><strong>Tên:</strong> <?= htmlspecialchars($user['name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($user['phone']) ?></p>
    <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($user['address']) ?></p>
    <p><strong>Vai trò:</strong> <?= htmlspecialchars($user['role']) ?></p>

    <?php if ($showForm): ?>
      <form method="post" enctype="multipart/form-data">
        <label>Tên:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Số điện thoại:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

        <label>Mật khẩu mới (nếu muốn đổi):</label>
        <input type="password" name="password">

        <label>Ảnh đại diện:</label>
        <input type="file" name="avatar" accept="image/*">

        <label>Địa chỉ:</label>
        <textarea name="address"><?= htmlspecialchars($user['address']) ?></textarea>

        <button type="submit" name="update">Cập nhật</button>
      </form>
    <?php else: ?>
      <form method="post">
        <button type="submit" name="edit">Chỉnh sửa thông tin</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
