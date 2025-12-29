<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Chỉ cho phép admin, staff, receptionist, doctor
if (!in_array($_SESSION['role'], ['admin','staff','receptionist','doctor'])) {
    echo "Bạn không có quyền truy cập trang này.";
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

$message = "";

// Thêm bệnh nhân
if (isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO patients (name, gender, birth_date, phone, address, email) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['gender'],
        $_POST['birth_date'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['email']
    ]);
    $message = "✅ Đã thêm bệnh nhân thành công.";
}

// Sửa bệnh nhân
if (isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE patients SET name=?, gender=?, birth_date=?, phone=?, address=?, email=? WHERE id=?");
    $stmt->execute([
        $_POST['name'],
        $_POST['gender'],
        $_POST['birth_date'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['email'],
        $_POST['id']
    ]);
    $message = "✅ Đã cập nhật thông tin bệnh nhân.";
}

// Xóa bệnh nhân
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM patients WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    $message = "🗑️ Đã xóa bệnh nhân.";
}

// Lấy danh sách bệnh nhân
$patients = $pdo->query("SELECT * FROM patients ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý bệnh nhân</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .message { padding:10px; margin:10px 0; border-radius:6px; }
    .success { background:#d4edda; color:#155724; }
    .error { background:#f8d7da; color:#721c24; }
    a.button {
      display:inline-block; padding:4px 8px; background:#007bff; color:white;
      border-radius:4px; text-decoration:none; margin-right:6px;
    }
    a.button:hover { background:#0056b3; }
    a.delete { background:#dc3545; }
    a.delete:hover { background:#a71d2a; }
    a.view { background:#28a745; }
    a.view:hover { background:#1e7e34; }
  </style>
</head>
<body>
<header>
  <h1>Quản lý bệnh nhân</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="users.php">Người dùng</a>
  <a href="services.php">Dịch vụ</a>
  <a href="appointments.php">Lịch hẹn</a>
  <a href="patients.php" class="active">Quản lí khách hàng</a>
  <a href="posts.php">Quản lí bài đăng</a>
  <a href="invoice.php">Hóa đơn</a>
  <a href="revenue.php">Doanh thu</a>
  <a href="quanlybacsi.php">Quản lí bác sĩ</a>
  <a href="tiepnhanlienhe.php">Tiếp nhận liên hệ</a>
  <a href="index.php">Trang khách hàng</a>
</nav>
<div class="container">
  <?php if ($message): ?>
    <div class="message <?= strpos($message,'❌')!==false ? 'error':'success' ?>">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <h2>Thêm bệnh nhân mới</h2>
    <form method="post">
      <input type="text" name="name" placeholder="Tên bệnh nhân" required>
      <select name="gender">
        <option value="male">Nam</option>
        <option value="female">Nữ</option>
        <option value="other">Khác</option>
      </select>
      <input type="date" name="birth_date">
      <input type="text" name="phone" placeholder="Số điện thoại">
      <input type="text" name="address" placeholder="Địa chỉ">
      <input type="email" name="email" placeholder="Email">
      <button type="submit" name="add">Thêm</button>
    </form>
  </div>

  <div class="card">
    <h2>Danh sách bệnh nhân</h2>
    <table>
      <tr>
        <th>ID</th><th>Tên</th><th>Giới tính</th><th>Ngày sinh</th>
        <th>Điện thoại</th><th>Địa chỉ</th><th>Email</th><th>Hành động</th>
      </tr>
      <?php if (count($patients) > 0): ?>
        <?php foreach ($patients as $p): ?>
          <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['gender']) ?></td>
            <td><?= $p['birth_date'] ?></td>
            <td><?= htmlspecialchars($p['phone']) ?></td>
            <td><?= htmlspecialchars($p['address']) ?></td>
            <td><?= htmlspecialchars($p['email']) ?></td>
            <td>
              <!-- Form sửa -->
              <form method="post" style="display:inline-block; margin-bottom:6px;">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>">
                <select name="gender">
                  <option value="male" <?= $p['gender']=='male'?'selected':'' ?>>Nam</option>
                  <option value="female" <?= $p['gender']=='female'?'selected':'' ?>>Nữ</option>
                  <option value="other" <?= $p['gender']=='other'?'selected':'' ?>>Khác</option>
                </select>
                <input type="date" name="birth_date" value="<?= $p['birth_date'] ?>">
                <input type="text" name="phone" value="<?= htmlspecialchars($p['phone']) ?>">
                <input type="text" name="address" value="<?= htmlspecialchars($p['address']) ?>">
                <input type="email" name="email" value="<?= htmlspecialchars($p['email']) ?>">
                <button type="submit" name="edit">Sửa</button>
              </form>
              <!-- Các nút hành động -->
              <div style="margin-top:6px;">
                <a href="patients.php?delete=<?= $p['id'] ?>" class="button delete" onclick="return confirm('Xóa bệnh nhân này?')">🗑️ Xóa</a>
                <a href="medical_records.php?patient_id=<?= $p['id'] ?>" class="button view">📄 Xem hồ sơ bệnh án</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8">Chưa có bệnh nhân nào.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>
</body>
</html>
