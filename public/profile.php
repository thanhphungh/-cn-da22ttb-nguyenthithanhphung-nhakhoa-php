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

// Lấy thông tin người dùng
$stmt = $pdo->prepare("SELECT id, name, email, role, avatar, address, phone FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Cập nhật thông tin
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
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Hồ sơ cá nhân</title>
<link rel="stylesheet" href="style.css">
<style>
.container { width:60%; margin:30px auto; }
.card { background:#fff; padding:20px; border:1px solid #ccc; border-radius:8px; }
img.avatar { max-width:120px; border-radius:50%; margin-bottom:10px; }
label { display:block; margin-top:10px; }
input[type="text"], input[type="email"], input[type="password"] { width:100%; padding:8px; }
textarea { width:100%; padding:8px; height:60px; }
button { margin-top:15px; padding:10px 15px; background:#007bff; color:#fff; border:none; border-radius:4px; }
button:hover { background:#0056b3; }
.message { background:#e0ffe0; padding:10px; margin-bottom:15px; border:1px solid #0c0; }
</style>
</head>
<body>
<header>
  <h1>Hồ sơ cá nhân</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
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

      <p><strong>Vai trò:</strong> <?= htmlspecialchars($user['role']) ?></p>

      <button type="submit" name="update">Cập nhật</button>
    </form>
  </div>
</div>
</body>
</html>
