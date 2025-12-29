<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Bạn chưa đăng nhập.");
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

$invoice_id = $_GET['invoice_id'] ?? $_GET['id'] ?? null;
if (!$invoice_id) {
    die("❌ Thiếu tham số invoice_id trên URL. Ví dụ: print_invoice.php?invoice_id=1");
}

// Lấy thông tin hóa đơn + tên bệnh nhân
$stmt = $pdo->prepare("
    SELECT i.id, i.patient_id, i.total, i.created_at,
           u.name AS patient_name
    FROM invoices i
    LEFT JOIN users u ON i.patient_id = u.id
    WHERE i.id = ?
");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    die("❌ Không tìm thấy hóa đơn với ID = " . htmlspecialchars($invoice_id));
}

// Gán mặc định nếu không có thông tin liên hệ
$patientPhone = '—';
$patientAddress = '—';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>In hóa đơn</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    h1 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table, th, td { border: 1px solid #000; }
    th, td { padding: 8px; text-align: left; }
    .footer { margin-top: 40px; text-align: center; }
  </style>
</head>
<body onload="window.print()">
  <h1>HÓA ĐƠN THANH TOÁN</h1>
  <p><strong>Mã hóa đơn:</strong> <?= htmlspecialchars($invoice['id']) ?></p>
  <p><strong>Bệnh nhân:</strong> <?= htmlspecialchars($invoice['patient_name'] ?? '—') ?></p>
  <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($patientPhone) ?></p>
  <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($patientAddress) ?></p>
  <p><strong>Ngày tạo:</strong> <?= htmlspecialchars($invoice['created_at']) ?></p>
  <hr>

  <table>
    <tr>
      <th>Dịch vụ</th>
      <th>Số lượng</th>
      <th>Đơn giá</th>
      <th>Thành tiền</th>
    </tr>
    <?php
    $details = $pdo->prepare("
        SELECT ii.service_id, ii.quantity, ii.price,
               s.name AS service_name
        FROM invoice_items ii
        LEFT JOIN services s ON ii.service_id = s.id
        WHERE ii.invoice_id = ?
    ");
    $details->execute([$invoice_id]);
    $hasDetails = false;
    foreach ($details as $row):
        $hasDetails = true; ?>
      <tr>
        <td><?= htmlspecialchars($row['service_name'] ?? 'Dịch vụ #'.$row['service_id']) ?></td>
        <td><?= (int)$row['quantity'] ?></td>
        <td><?= number_format((float)$row['price'], 0, ',', '.') ?> đ</td>
        <td><?= number_format((float)$row['quantity'] * (float)$row['price'], 0, ',', '.') ?> đ</td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$hasDetails): ?>
      <tr><td colspan="4">❌ Hóa đơn này chưa có chi tiết dịch vụ.</td></tr>
    <?php endif; ?>
  </table>

  <h3>Tổng cộng: <?= number_format((float)$invoice['total'], 0, ',', '.') ?> đ</h3>

  <div class="footer">
    <p>Cảm ơn quý khách đã sử dụng dịch vụ!</p>
  </div>
</body>
</html>
