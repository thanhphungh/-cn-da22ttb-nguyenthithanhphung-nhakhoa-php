<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Chỉ admin mới được truy cập trang này
if ($_SESSION['role'] !== 'admin') {
    echo "Bạn không có quyền truy cập trang này.";
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = $error = null;

// Thêm người dùng
if (isset($_POST['add'])) {
    $email = $_POST['email'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email=?");
    $check->execute([$email]);
    if ($check->fetchColumn() > 0) {
        $error = "Email đã tồn tại!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $email,
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['role']
        ]);
        $success = "Đã thêm người dùng thành công!";
    }
}

// Sửa người dùng
if (isset($_POST['edit'])) {
    if (!empty($_POST['password'])) {
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?");
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['role'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['id']
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['role'],
            $_POST['id']
        ]);
    }
    $success = "Đã cập nhật thông tin người dùng!";
}

// Xóa người dùng
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    $success = "Đã xóa người dùng!";
}

// Lấy danh sách người dùng
$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý người dùng</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .msg-success { background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin:10px 0; }
    .msg-error { background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin:10px 0; }
  </style>
</head>
<body>
<header>
  <h1>Quản lý người dùng</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="users.php" class="active">Người dùng</a>
  <a href="services.php">Dịch vụ</a>
  <a href="appointments.php">Lịch hẹn</a>
  <a href="patients.php">Quản lí khách hàng</a>
  <a href="posts.php">Quản lí bài đăng</a>
  <a href="invoice.php">Hóa đơn</a>
  <a href="revenue.php">Doanh thu</a>
  <a href="quanlybacsi.php">Quản lí bác sĩ</a>
  <a href="tiepnhanlienhe.php">Tiếp nhận liên hệ</a>
  <a href="index.php">Trang khách hàng</a>
</nav>
<div class="container">

  <?php if ($success): ?>
    <div class="msg-success"><?= $success ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="msg-error"><?= $error ?></div>
  <?php endif; ?>

  <div class="card">
    <h2>Thêm người dùng mới</h2>
    <form method="post">
      <input type="text" name="name" placeholder="Tên" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Mật khẩu" required>
      <select name="role">
        <option value="admin">Admin</option>
        <option value="doctor">Bác sĩ</option>
        <option value="staff">Nhân viên</option>
        <option value="accountant">Kế toán</option>
        <option value="receptionist">Lễ tân</option>
        <option value="patient">Khách hàng</option>
      </select>
      <button type="submit" name="add">Thêm</button>
    </form>
  </div>

  <div class="card">
    <h2>Danh sách người dùng</h2>
    <table>
      <tr>
        <th>ID</th><th>Tên</th><th>Email</th><th>Vai trò</th><th>Hành động</th>
      </tr>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
          <td>
            <!-- Form chỉnh sửa -->
            <form method="post" style="display:inline-block">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <input type="text" name="name" value="<?= htmlspecialchars($u['name']) ?>">
              <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>">
              <input type="password" name="password" placeholder="Mật khẩu mới (nếu đổi)">
              <select name="role">
                <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                <option value="doctor" <?= $u['role']=='doctor'?'selected':'' ?>>Bác sĩ</option>
                <option value="staff" <?= $u['role']=='staff'?'selected':'' ?>>Nhân viên</option>
                <option value="accountant" <?= $u['role']=='accountant'?'selected':'' ?>>Kế toán</option>
                <option value="receptionist" <?= $u['role']=='receptionist'?'selected':'' ?>>Lễ tân</option>
                <option value="patient" <?= $u['role']=='patient'?'selected':'' ?>>Khách hàng</option>
              </select>
              <button type="submit" name="edit">Sửa</button>
            </form>
            <!-- Nút xóa -->
            <a href="users.php?delete=<?= $u['id'] ?>" onclick="return confirmDelete('Xóa người dùng này?')">Xóa</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
<script>
function confirmDelete(msg) {
  return confirm(msg);
}
</script>
</body>
</html>
