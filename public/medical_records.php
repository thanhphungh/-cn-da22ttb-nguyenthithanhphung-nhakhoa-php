<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!in_array($_SESSION['role'], ['admin','doctor','staff'])) {
    echo "Bạn không có quyền truy cập trang này.";
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message = "";

// Lấy patient_id từ URL nếu có
$selected_patient_id = $_GET['patient_id'] ?? null;

// Thêm hồ sơ bệnh án
if (isset($_POST['add'])) {
    $xray = null;
    if (!empty($_FILES['xray']['name'])) {
        $target = "uploads/" . time() . "_" . basename($_FILES['xray']['name']);
        move_uploaded_file($_FILES['xray']['tmp_name'], $target);
        $xray = $target;
    }

    $stmt = $pdo->prepare("INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, xray_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['patient_id'],
        $_POST['doctor_id'],
        $_POST['diagnosis'],
        $_POST['treatment'],
        $xray
    ]);
    $message = "✅ Đã thêm hồ sơ bệnh án.";
}

// Sửa hồ sơ bệnh án
if (isset($_POST['edit'])) {
    $xray = $_POST['old_xray'] ?? null;
    if (!empty($_FILES['xray']['name'])) {
        $target = "uploads/" . time() . "_" . basename($_FILES['xray']['name']);
        move_uploaded_file($_FILES['xray']['tmp_name'], $target);
        $xray = $target;
    }

    $stmt = $pdo->prepare("UPDATE medical_records SET diagnosis=?, treatment=?, xray_image=? WHERE id=?");
    $stmt->execute([
        $_POST['diagnosis'],
        $_POST['treatment'],
        $xray,
        $_POST['id']
    ]);
    $message = "✅ Đã cập nhật hồ sơ bệnh án.";
}

// Xóa hồ sơ bệnh án
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM medical_records WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    $message = "🗑️ Đã xóa hồ sơ bệnh án.";
}

// Lấy danh sách hồ sơ bệnh án theo bệnh nhân
if ($selected_patient_id) {
    $stmt = $pdo->prepare("
        SELECT mr.*, p.name AS patient_name, d.name AS doctor_name
        FROM medical_records mr
        JOIN patients p ON mr.patient_id = p.id
        LEFT JOIN users d ON mr.doctor_id = d.id
        WHERE mr.patient_id = ?
        ORDER BY mr.created_at DESC
    ");
    $stmt->execute([$selected_patient_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $records = $pdo->query("
        SELECT mr.*, p.name AS patient_name, d.name AS doctor_name
        FROM medical_records mr
        JOIN patients p ON mr.patient_id = p.id
        LEFT JOIN users d ON mr.doctor_id = d.id
        ORDER BY mr.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy danh sách bệnh nhân và bác sĩ
$patients = $pdo->query("SELECT id, name FROM patients ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$doctors = $pdo->query("SELECT id, name FROM users WHERE role='doctor' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Tạo map id->name cho bệnh nhân
$patientMap = [];
foreach ($patients as $p) {
    $patientMap[$p['id']] = $p['name'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Hồ sơ bệnh án</title>
<link rel="stylesheet" href="style.css">
<style>
textarea { width:100%; height:60px; }
img.xray { max-width:120px; border:1px solid #ccc; margin-top:5px; }
</style>
</head>
<body>
<header>
  <h1>Quản lý hồ sơ điều trị</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="patients.php">Khách hàng</a>
  <a href="medical_records.php" class="active">Hồ sơ điều trị</a>
</nav>
<div class="container">
  <?php if ($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>

  <div class="card">
    <h2>Thêm hồ sơ điều trị</h2>
    <form method="post" enctype="multipart/form-data">
      <?php if ($selected_patient_id): ?>
        <p><strong>Khách hàng:</strong> <?= htmlspecialchars($patientMap[$selected_patient_id] ?? '') ?></p>
        <input type="hidden" name="patient_id" value="<?= $selected_patient_id ?>">
      <?php else: ?>
        <select name="patient_id" required>
          <option value="">-- Chọn khách nhàng --</option>
          <?php foreach ($patients as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <select name="doctor_id" required>
        <option value="">-- Chọn bác sĩ --</option>
        <?php foreach ($doctors as $d): ?>
          <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <textarea name="diagnosis" placeholder="Thông tin chẩn đoán" required></textarea>
      <textarea name="treatment" placeholder="Phác đồ điều trị" required></textarea>
      <input type="file" name="xray" accept="image/*">
      <button type="submit" name="add">Thêm</button>
    </form>
  </div>

  <div class="card">
    <h2>Danh sách hồ sơ điều trị <?= $selected_patient_id ? 'của bệnh nhân: '.htmlspecialchars($patientMap[$selected_patient_id]) : '' ?></h2>
    <table>
      <tr>
        <th>ID</th><th>khách hàng</th><th>Bác sĩ</th><th>Chẩn đoán</th><th>Điều trị</th><th>X-quang</th><th>Ngày tạo</th><th>Hành động</th>
      </tr>
      <?php if (count($records) > 0): ?>
        <?php foreach ($records as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['patient_name']) ?></td>
            <td><?= htmlspecialchars($r['doctor_name']) ?></td>
            <td><?= nl2br(htmlspecialchars($r['diagnosis'])) ?></td>
            <td><?= nl2br(htmlspecialchars($r['treatment'])) ?></td>
            <td>
              <?php if ($r['xray_image']): ?>
                <img src="<?= $r['xray_image'] ?>" class="xray">
              <?php endif; ?>
            </td>
            <td><?= $r['created_at'] ?></td>
            <td>
              <form method="post" enctype="multipart/form-data" style="margin-bottom:6px;">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <input type="hidden" name="old_xray" value="<?= $r['xray_image'] ?>">
                <textarea name="diagnosis"><?= htmlspecialchars($r['diagnosis']) ?></textarea>
                <textarea name="treatment"><?= htmlspecialchars($r['treatment']) ?></textarea>
                <input type="file" name="xray" accept="image/*">
                <button type="submit" name="edit">Sửa</button>
              </form>
              <a href="medical_records.php?delete=<?= $r['id'] ?><?= $selected_patient_id ? '&patient_id='.$selected_patient_id : '' ?>" onclick="return confirm('Xóa hồ sơ này?')">🗑️ Xóa</a>
              <a href="print_record.php?id=<?= $r['id'] ?>" target="_blank">🖨️ In</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8">Chưa có hồ sơ bệnh án.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>
</body>
</html>
