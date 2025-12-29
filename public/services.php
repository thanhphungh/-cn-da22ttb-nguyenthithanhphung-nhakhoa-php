<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Chỉ admin, staff, accountant mới được vào
if (!in_array($_SESSION['role'], ['admin','staff','accountant'])) {
    echo "Bạn không có quyền truy cập trang này.";
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = $error = null;

// Thêm dịch vụ
if (isset($_POST['add'])) {
    if (empty($_POST['name']) || empty($_POST['description']) || $_POST['price'] <= 0) {
        $error = "Vui lòng nhập đầy đủ thông tin và giá phải > 0.";
    } else {
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $targetFile = $targetDir . time() . "_" . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
            $imagePath = $targetFile;
        }
        $stmt = $pdo->prepare("INSERT INTO services (name, description, price, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['description'], $_POST['price'], $imagePath]);
        $success = "Đã thêm dịch vụ thành công!";
    }
}

// Sửa dịch vụ
if (isset($_POST['edit'])) {
    if (empty($_POST['name']) || empty($_POST['description']) || $_POST['price'] <= 0) {
        $error = "Vui lòng nhập đầy đủ thông tin và giá phải > 0.";
    } else {
        $imagePath = $_POST['old_image'];
        if (!empty($_FILES['image']['name'])) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $targetFile = $targetDir . time() . "_" . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
            $imagePath = $targetFile;
        }
        $stmt = $pdo->prepare("UPDATE services SET name=?, description=?, price=?, image=? WHERE id=?");
        $stmt->execute([$_POST['name'], $_POST['description'], $_POST['price'], $imagePath, $_POST['id']]);
        $success = "Đã cập nhật dịch vụ thành công!";
    }
}

// Xóa dịch vụ
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    $success = "Đã xóa dịch vụ!";
}

// Lấy danh sách dịch vụ
$services = $pdo->query("SELECT * FROM services")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý dịch vụ</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .msg-success { background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin:10px 0; }
    .msg-error { background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin:10px 0; }
  </style>
</head>
<body>
<header>
  <h1>Quản lý dịch vụ</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="users.php">Người dùng</a>
  <a href="services.php" class="active">Dịch vụ</a>
  <a href="appointments.php">Lịch hẹn</a>
  <a href="patients.php">Quản lí bệnh khách hàng</a>
  <a href="posts.php">Quản lí bài đăng</a>
  <a href="revenue.php">Doanh thu</a>
  <a href="invoice.php">Hóa đơn</a>
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
    <h2>Thêm dịch vụ mới</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="text" name="name" placeholder="Tên dịch vụ" required>
      <input type="text" name="description" placeholder="Mô tả" required>
      <input type="number" name="price" placeholder="Giá" required>
      <input type="file" name="image" accept="image/*">
      <button type="submit" name="add">Thêm</button>
    </form>
  </div>

  <div class="card">
    <h2>Danh sách dịch vụ</h2>
    <table>
      <tr><th>ID</th><th>Tên</th><th>Mô tả</th><th>Giá</th><th>Ảnh</th><th>Hành động</th></tr>
      <?php foreach ($services as $s): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= htmlspecialchars($s['description']) ?></td>
          <td><?= number_format($s['price'], 0, ',', '.') ?> VNĐ</td>
          <td><?php if ($s['image']): ?><img src="<?= $s['image'] ?>" style="max-width:100px;"><?php endif; ?></td>
          <td>
            <!-- Form sửa -->
            <form method="post" enctype="multipart/form-data" style="display:inline-block">
              <input type="hidden" name="id" value="<?= $s['id'] ?>">
              <input type="hidden" name="old_image" value="<?= $s['image'] ?>">
              <input type="text" name="name" value="<?= htmlspecialchars($s['name']) ?>">
              <input type="text" name="description" value="<?= htmlspecialchars($s['description']) ?>">
              <input type="number" name="price" value="<?= $s['price'] ?>">
              <input type="file" name="image" accept="image/*">
              <button type="submit" name="edit">Sửa</button>
            </form>
            <!-- Nút xóa -->
            <a href="services.php?delete=<?= $s['id'] ?>" onclick="return confirmDelete('Xóa dịch vụ này?')">Xóa</a>
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
