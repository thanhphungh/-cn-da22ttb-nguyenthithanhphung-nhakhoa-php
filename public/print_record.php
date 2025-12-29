<?php
$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT mr.*, 
           p.name AS patient_name, p.gender, p.birth_date, p.phone, p.address,
           d.name AS doctor_name
    FROM medical_records mr
    JOIN patients p ON mr.patient_id = p.id
    LEFT JOIN users d ON mr.doctor_id = d.id
    WHERE mr.id = ?
");
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    die("❌ Không tìm thấy hồ sơ bệnh án.");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>In hồ sơ bệnh án</title>
<style>
body { 
  font-family: Arial, sans-serif; 
  margin:0; 
  padding:0; 
  background:#f5f5f5;
}
.container {
  width: 70%;
  margin: 40px auto;
  background: white;
  padding: 20px;
  border: 2px solid #333;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0,0,0,0.2);
  position: relative;
}
h2 { text-align:center; margin-bottom:20px; }
.section { margin-bottom:20px; }
.xray {
  position: absolute;
  bottom: 20px;
  right: 20px;
  max-width: 200px;
  border: 1px solid #ccc;
}
.button-back {
  display:inline-block;
  padding:8px 12px;
  background:#007bff;
  color:white;
  border-radius:4px;
  text-decoration:none;
  margin:20px auto;
  display:block;
  width:max-content;
}
.button-back:hover { background:#0056b3; }
</style>
</head>
<body onload="window.print()">
<div class="container">
  <h2>HỒ SƠ BỆNH ÁN</h2>

  <?php if (!empty($record['xray_image'])): ?>
    <img src="<?= htmlspecialchars($record['xray_image']) ?>" class="xray" alt="Ảnh X-quang">
  <?php endif; ?>

  <div class="section">
    <strong>Bệnh nhân:</strong> <?= htmlspecialchars($record['patient_name']) ?><br>
    <strong>Giới tính:</strong> <?= htmlspecialchars($record['gender']) ?><br>
    <strong>Ngày sinh:</strong> <?= $record['birth_date'] ?><br>
    <strong>Điện thoại:</strong> <?= htmlspecialchars($record['phone']) ?><br>
    <strong>Địa chỉ:</strong> <?= htmlspecialchars($record['address']) ?><br>
  </div>

  <div class="section">
    <strong>Bác sĩ phụ trách:</strong> <?= htmlspecialchars($record['doctor_name']) ?><br>
    <strong>Ngày tạo hồ sơ:</strong> <?= $record['created_at'] ?><br>
  </div>

  <div class="section">
    <h3>Chẩn đoán</h3>
    <p><?= nl2br(htmlspecialchars($record['diagnosis'])) ?></p>
  </div>

  <div class="section">
    <h3>Phác đồ điều trị</h3>
    <p><?= nl2br(htmlspecialchars($record['treatment'])) ?></p>
  </div>
</div>
</body>
</html>
