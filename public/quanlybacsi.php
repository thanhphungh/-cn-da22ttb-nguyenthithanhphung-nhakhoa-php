<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}

$success = null;

// Thêm bác sĩ
if (isset($_POST['add'])) {
    $avatar = null;
    if (!empty($_FILES['avatar']['name'])) {
        $targetDir = "uploads/";
        $avatar = time() . "_" . basename($_FILES["avatar"]["name"]);
        move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetDir . $avatar);
    }
    $stmt = $pdo->prepare("INSERT INTO doctors (name, specialty, phone, email, avatar) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['specialty'],
        $_POST['phone'],
        $_POST['email'],
        $avatar
    ]);
    $success = "Đã thêm bác sĩ mới!";
}

// Sửa bác sĩ
if (isset($_POST['edit'])) {
    $avatar = $_POST['old_avatar'];
    if (!empty($_FILES['avatar']['name'])) {
        $targetDir = "uploads/";
        $avatar = time() . "_" . basename($_FILES["avatar"]["name"]);
        move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetDir . $avatar);
    }
    $stmt = $pdo->prepare("UPDATE doctors SET name=?, specialty=?, phone=?, email=?, avatar=? WHERE id=?");
    $stmt->execute([
        $_POST['name'],
        $_POST['specialty'],
        $_POST['phone'],
        $_POST['email'],
        $avatar,
        $_POST['id']
    ]);
    $success = "Đã cập nhật thông tin bác sĩ!";
}

// Xóa bác sĩ
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM doctors WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    $success = "Đã xóa bác sĩ!";
}

// Lấy danh sách bác sĩ
$doctors = $pdo->query("SELECT * FROM doctors ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý bác sĩ</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { margin:0; padding:0; background:#f5f5f5; font-family:sans-serif; }
    header {
      background: #007BFF;
      color: white;
      padding: 12px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header .logout {
      background: #dc3545;
      color: white;
      padding: 8px 12px;
      border-radius: 6px;
      text-decoration: none;
    }
    nav {
      background: #333;
      padding: 10px 20px;
    }
    nav a {
      color: white;
      margin-right: 15px;
      text-decoration: none;
      font-weight: 500;
    }
    nav a.active { text-decoration: underline; }
    nav a:hover { color: #ffc107; }
    .container { max-width: 1100px; margin: 20px auto; }
    .card {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      margin-bottom: 20px;
    }
    .msg-success {
      background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:10px;
    }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f7f7f7; }
    img.avatar { width:50px; height:50px; border-radius:50%; object-fit:cover; }
    form input, form button { padding: 6px; margin-right: 8px; margin-bottom: 8px; }
    form button {
      background: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer;
    }
    form button:hover { background:#0056b3; }
  </style>
</head>
<body>
<header>
  <h1>Quản lý bác sĩ</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="users.php">Người dùng</a>
  <a href="services.php">Dịch vụ</a>
  <a href="appointments.php">Lịch hẹn</a>
  <a href="patients.php">Quản lí khách hàng</a>
  <a href="posts.php">Quản lí bài đăng</a>
  <a href="revenue.php">Doanh thu</a>
  <a href="invoice.php">Hóa đơn</a>
  <a href="quanlybacsi.php">Quản lí bác sĩ</a>
  <a href="tiepnhanlienhe.php">Tiếp nhận liên hệ</a>
  <a href="doctors.php" class="active">Bác sĩ</a>
  <a href="index.php">Trang khách hàng</a>
</nav>

<div class="container">

  <?php if ($success): ?>
    <div class="msg-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <div class="card">
    <h2>Thêm bác sĩ mới</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="text" name="name" placeholder="Tên bác sĩ" required>
      <input type="text" name="specialty" placeholder="Chuyên khoa" required>
      <input type="text" name="phone" placeholder="Số điện thoại">
      <input type="email" name="email" placeholder="Email">
      <input type="file" name="avatar">
      <button type="submit" name="add">Thêm</button>
    </form>
  </div>

  <div class="card">
    <h2>Danh sách bác sĩ</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Ảnh</th>
          <th>Tên</th>
          <th>Chuyên khoa</th>
          <th>Điện thoại</th>
          <th>Email</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($doctors as $doc): ?>
          <tr>
            <td><?= $doc['id'] ?></td>
            <td>
              <?php if (!empty($doc['avatar'])): ?>
                <img src="uploads/<?= htmlspecialchars($doc['avatar']) ?>" class="avatar" alt="Avatar">
              <?php else: ?>
                <span>Chưa có ảnh</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($doc['name']) ?></td>
            <td><?= htmlspecialchars($doc['specialty']) ?></td>
            <td><?= htmlspecialchars($doc['phone']) ?></td>
            <td><?= htmlspecialchars($doc['email']) ?></td>
            <td>
              <form method="post" enctype="multipart/form-data" style="display:inline-block;">
                <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                <input type="hidden" name="old_avatar" value="<?= htmlspecialchars($doc['avatar']) ?>">
                <input type="text" name="name" value="<?= htmlspecialchars($doc['name']) ?>" required>
                <input type="text" name="specialty" value="<?= htmlspecialchars($doc['specialty']) ?>" required>
                <input type="text" name="phone" value="<?= htmlspecialchars($doc['phone']) ?>">
                <input type="email" name="email" value="<?= htmlspecialchars($doc['email']) ?>">
                <input type="file" name="avatar">
                <button type="submit" name="edit">Sửa</button>
              </form>
              <a href="doctors.php?delete=<?= $doc['id'] ?>" onclick="return confirm('Xóa bác sĩ này?')" style="color:red;">Xóa</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
