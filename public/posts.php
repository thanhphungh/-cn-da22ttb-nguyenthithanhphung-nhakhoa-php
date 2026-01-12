<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    echo "Bạn không có quyền truy cập trang này.";
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}

// Thêm bài viết
if (isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, image, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([
        $_POST['title'],
        $_POST['content'],
        $_FILES['image']['name'] ?? null
    ]);
    if (!empty($_FILES['image']['name'])) {
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $_FILES['image']['name']);
    }
}

// Sửa bài viết
if (isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE posts SET title=?, content=?, image=? WHERE id=?");
    $stmt->execute([
        $_POST['title'],
        $_POST['content'],
        !empty($_FILES['image']['name']) ? $_FILES['image']['name'] : $_POST['old_image'],
        $_POST['id']
    ]);
    if (!empty($_FILES['image']['name'])) {
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $_FILES['image']['name']);
    }
}

// Xóa bài viết
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id=?");
    $stmt->execute([$_GET['delete']]);
}

$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý bài đăng</title>
  <link rel="stylesheet" href="style.css">
  <style>
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; vertical-align: top; }
    th { background: #007BFF; color: #fff; }
    .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
    .btn-edit { background: orange; color: #fff; }
    .btn-delete { background: red; color: #fff; }
    .btn-add { background: green; color: #fff; }
    .card { margin-bottom: 30px; }
  </style>
</head>
<body>
<header>
  <h1>Quản lý bài đăng</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="users.php">Người dùng</a>
  <a href="services.php">Dịch vụ</a>
  <a href="appointments.php">Lịch hẹn</a>
  <a href="patients.php">Quản lí bệnh khách hàng</a>
  <a href="posts.php" class="active">Quản lí bài đăng</a>
  <a href="invoice.php">Hóa đơn</a>
  <a href="revenue.php">Doanh thu</a>
  <a href="quanlybacsi.php">Quản lí bác sĩ</a>
  <a href="tiepnhanlienhe.php">Tiếp nhận liên hệ</a>
  <a href="index.php">Trang khách hàng</a>
</nav>
<div class="container">
  <div class="card">
    <h2>Thêm bài viết mới</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="text" name="title" placeholder="Tiêu đề" required><br><br>
      <textarea name="content" placeholder="Nội dung" required></textarea><br><br>
      <input type="file" name="image"><br><br>
      <button type="submit" name="add" class="btn btn-add">Thêm</button>
    </form>
  </div>

  <div class="card">
    <h2>Danh sách bài viết</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Tiêu đề</th><th>Nội dung</th><th>Ảnh</th><th>Ngày tạo</th><th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
          <tr>
            <td><?= $post['id'] ?></td>
            <td><?= htmlspecialchars($post['title']) ?></td>
            <td><?= nl2br(htmlspecialchars($post['content'])) ?></td>
            <td>
              <?php if (!empty($post['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($post['image']) ?>" width="80">
              <?php endif; ?>
            </td>
            <td><?= $post['created_at'] ?></td>
            <td>
              <!-- Form sửa -->
              <form method="post" enctype="multipart/form-data" style="display:inline-block; text-align:left;">
                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required><br>
                <textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea><br>
                <input type="hidden" name="old_image" value="<?= htmlspecialchars($post['image']) ?>">
                <input type="file" name="image"><br>
                <button type="submit" name="edit" class="btn btn-edit">Sửa</button>
              </form>
              <!-- Nút xóa -->
              <a href="posts.php?delete=<?= $post['id'] ?>" class="btn btn-delete" onclick="return confirm('Xóa bài viết này?')">Xóa</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
