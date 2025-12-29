<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}

// Danh sách bệnh nhân, bác sĩ, dịch vụ
$patients = $pdo->query("SELECT id, name FROM users WHERE role IN ('user','patient')")->fetchAll(PDO::FETCH_ASSOC);
$doctors  = $pdo->query("SELECT id, name FROM doctors")->fetchAll(PDO::FETCH_ASSOC);
$services = $pdo->query("SELECT id, name FROM services")->fetchAll(PDO::FETCH_ASSOC);

$success = null;

// Thêm lịch hẹn
if (isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, service_id, date, time, note, status)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['patient_id'],
        $_POST['doctor_id'],
        $_POST['service_id'],
        $_POST['date'],
        $_POST['time'],
        $_POST['note'],
        $_POST['status'] // must be one of: pending, confirmed, done, cancel
    ]);
    $success = "Đã thêm lịch hẹn mới!";
}

// Sửa lịch hẹn
if (isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE appointments
                           SET patient_id=?, doctor_id=?, service_id=?, date=?, time=?, note=?, status=?
                           WHERE id=?");
    $stmt->execute([
        $_POST['patient_id'],
        $_POST['doctor_id'],
        $_POST['service_id'],
        $_POST['date'],
        $_POST['time'],
        $_POST['note'],
        $_POST['status'],
        $_POST['id']
    ]);
    $success = "Đã cập nhật lịch hẹn!";
}

// Cập nhật trạng thái
if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE appointments SET status=? WHERE id=?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
    if ($stmt->rowCount() > 0) {
        $success = "Đã cập nhật trạng thái lịch hẹn #" . intval($_POST['id']);
    } else {
        $success = "Không thể cập nhật trạng thái. Kiểm tra lại dữ liệu.";
    }
}

// Xóa lịch hẹn
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    $success = "Đã xóa lịch hẹn!";
}

// Lấy danh sách lịch hẹn
$appointments = $pdo->query("
    SELECT a.*,
           u.name AS patient_name,
           d.name AS doctor_name,
           s.name AS service_name
    FROM appointments a
    JOIN users u   ON a.patient_id = u.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN services s ON a.service_id = s.id
    ORDER BY a.date ASC, a.time ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Hiển thị tiếng Việt cho trạng thái
function translateStatus($status) {
    switch ($status) {
        case 'pending':   return 'Chờ';
        case 'confirmed': return 'Đã duyệt';
        case 'done':      return 'Hoàn thành';
        case 'cancel':    return 'Hủy';
        default:          return $status;
    }
}

// Màu trạng thái
function statusColor($status) {
    switch ($status) {
        case 'pending':   return 'orange';
        case 'confirmed': return 'dodgerblue';
        case 'done':      return 'green';
        case 'cancel':    return 'red';
        default:          return '#333';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý lịch hẹn</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .msg-success { background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #f7f7f7; }
    table select, table button { font-size:14px; padding:4px 8px; }
    table button { background:#007BFF; color:#fff; border:none; border-radius:4px; cursor:pointer; }
    table button:hover { background:#0056b3; }
    .container { max-width: 1100px; margin: 0 auto; }
    .card { background:#fff; padding:16px; border:1px solid #eee; border-radius:8px; margin-bottom:16px; }
    .card h2 { margin-top:0; }
    .card form select, .card form input, .card form button { margin-right:8px; margin-bottom:8px; }
  </style>
</head>
<body>
<header>
  <h1>Quản lý lịch hẹn</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="users.php">Người dùng</a>
  <a href="services.php">Dịch vụ</a>
  <a href="appointments.php" class="active">Lịch hẹn</a>
  <a href="patients.php">Quản lí khách hàng</a>
  <a href="posts.php">Quản lí bài đăng</a>
  <a href="invoice.php">Hóa đơn</a>
  <a href="revenue.php">Doanh thu</a>
  <a href="quanlybacsi.php">Quản lí bác sĩ</a>
  <a href="tiepnhanlienhe.php">Tiếp nhận liên hệ</a>
  <a href="index.php">Trang khách hàng</a>
</nav>

<div class="container">

  <?php if (!empty($success)): ?>
    <div class="msg-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <div class="card">
    <h2>Thêm lịch hẹn mới</h2>
    <form method="post">
      <select name="patient_id" required>
        <option value="">-- Chọn bệnh nhân --</option>
        <?php foreach ($patients as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <select name="doctor_id" required>
        <option value="">-- Chọn bác sĩ --</option>
        <?php foreach ($doctors as $d): ?>
          <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <select name="service_id" required>
        <option value="">-- Chọn dịch vụ --</option>
        <?php foreach ($services as $s): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <input type="date" name="date" required>
      <input type="time" name="time" required>
      <input type="text" name="note" placeholder="Ghi chú">

      <select name="status" required>
        <option value="pending">Chờ</option>
        <option value="confirmed">Đã duyệt</option>
        <option value="done">Hoàn thành</option>
        <option value="cancel">Hủy</option>
      </select>

      <button type="submit" name="add">Thêm</button>
    </form>
  </div>

  <div class="card">
    <h2>Danh sách lịch hẹn</h2>
    <table id="appointmentsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Bệnh nhân</th>
          <th>Bác sĩ</th>
          <th>Dịch vụ</th>
          <th>Ngày</th>
          <th>Giờ</th>
          <th>Ghi chú</th>
          <th>Trạng thái</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($appointments as $app): ?>
          <tr>
            <td><?= $app['id'] ?></td>
            <td><?= htmlspecialchars($app['patient_name']) ?></td>
            <td><?= htmlspecialchars($app['doctor_name']) ?></td>
            <td><?= htmlspecialchars($app['service_name']) ?></td>
            <td><?= htmlspecialchars($app['date']) ?></td>
            <td><?= htmlspecialchars($app['time']) ?></td>
            <td><?= htmlspecialchars($app['note']) ?></td>
            <td style="color:<?= statusColor($app['status']) ?>; font-weight:bold;">
              <?= htmlspecialchars(translateStatus($app['status'])) ?>
            </td>
            <td>
              <form method="post" style="display:flex; gap:6px; align-items:center;">
                <input type="hidden" name="id" value="<?= $app['id'] ?>">
                <select name="status" required>
                  <option value="pending"   <?= $app['status']=='pending'   ? 'selected' : '' ?>>Chờ</option>
                  <option value="confirmed" <?= $app['status']=='confirmed' ? 'selected' : '' ?>>Đã duyệt</option>
                  <option value="done"      <?= $app['status']=='done'      ? 'selected' : '' ?>>Hoàn thành</option>
                  <option value="cancel"    <?= $app['status']=='cancel'    ? 'selected' : '' ?>>Hủy</option>
                </select>
                <button type="submit" name="update_status" value="1">Cập nhật</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
