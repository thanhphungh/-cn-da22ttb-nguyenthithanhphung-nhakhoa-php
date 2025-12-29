<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    echo "Bạn không có quyền truy cập trang này.";
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}

// Lấy danh sách user để hiển thị trong form
$users = $pdo->query("SELECT id, name FROM users")->fetchAll(PDO::FETCH_ASSOC);

// Thêm hóa đơn mới
if (isset($_POST['add_invoice'])) {
    $stmt = $pdo->prepare("INSERT INTO invoices (patient_id, total, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([
        $_POST['patient_id'],
        $_POST['total']
    ]);
    $successMessage = "Đã thêm hóa đơn thành công!";
}

// Tổng doanh thu
$totalStmt = $pdo->query("SELECT SUM(total) AS doanh_thu FROM invoices");
$total = $totalStmt->fetchColumn();

// Bộ lọc tuần
$selectedWeek = $_GET['week'] ?? date('Y-\WW');
list($year, $week) = explode('-W', $selectedWeek);

// Doanh thu theo ngày trong tuần được chọn
$weeklyDaysStmt = $pdo->prepare("
    SELECT DATE(created_at) AS ngay, DAYNAME(created_at) AS thu, SUM(total) AS doanh_thu
    FROM invoices
    WHERE YEAR(created_at) = ? AND WEEK(created_at,1) = ?
    GROUP BY DATE(created_at), DAYNAME(created_at)
    ORDER BY DATE(created_at)
");
$weeklyDaysStmt->execute([$year, $week]);
$weeklyDaysRevenue = $weeklyDaysStmt->fetchAll(PDO::FETCH_ASSOC);

// Doanh thu theo tuần trong tháng
$weeklyStmt = $pdo->query("
    SELECT YEAR(created_at) AS nam, MONTH(created_at) AS thang, WEEK(created_at,1) AS tuan, SUM(total) AS doanh_thu
    FROM invoices
    GROUP BY YEAR(created_at), MONTH(created_at), WEEK(created_at,1)
    ORDER BY nam ASC, thang ASC, tuan ASC
");
$weeklyRevenue = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thống kê doanh thu</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
    header { background: #0066cc; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
    header h1 { margin: 0; }
    .logout { background: #ff3333; color: #fff; padding: 6px 12px; border-radius: 4px; text-decoration: none; }
    .logout:hover { background: #c0392b; }
    nav { background: #333; padding: 10px 20px; text-align: center; }
    nav a { color: #fff; margin: 0 15px; text-decoration: none; font-weight: bold; }
    nav a:hover { text-decoration: underline; }
    h1 { text-align: center; margin-top: 20px; color: #ffffffff; }
    .box { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin: 30px auto; max-width: 1000px; }
    .box h2 { margin-top: 0; color: #0066cc; font-size: 22px; border-bottom: 2px solid #eee; padding-bottom: 5px; }
    .highlight { font-size: 28px; font-weight: bold; color: #28a745; text-align: center; margin-top: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    table th, table td { border: 1px solid #ddd; padding: 10px; text-align: center; }
    table th { background: #0066cc; color: #fff; }
    table tr:nth-child(even) { background: #f9f9f9; }
    table tr:hover { background: #e6f2ff; }
    .chart-container { max-width: 800px; margin: 30px auto; }
    canvas { width: 100% !important; height: auto !important; display: block; }
    .success { background:#d4edda;color:#155724;padding:10px;border-radius:5px;margin:20px auto;max-width:600px;text-align:center; }
    form input, form select { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px; }
    form button { padding: 8px 16px; background:#28a745; color:#fff; border:none; border-radius:4px; cursor:pointer; }
    form button:hover { background:#218838; }
  </style>
</head>
<body>
<header>
  <h1>Doanh thu</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="users.php">Người dùng</a>
  <a href="services.php">Dịch vụ</a>
  <a href="appointments.php">Lịch hẹn</a>
  <a href="patients.php">Quản lí khách hàng</a>
  <a href="posts.php">Quản lí bài đăng</a>
  <a href="invoice.php">Hóa đơn</a>
  <a href="revenue.php" class="active">Doanh thu</a>
  <a href="quanlybacsi.php">Quản lí bác sĩ</a>
  <a href="tiepnhanlienhe.php">Tiếp nhận liên hệ</a>
  <a href="index.php">Trang khách hàng</a>
</nav>

<h1>Thống kê doanh thu</h1>

<?php if (isset($successMessage)): ?>
  <div class="success"><?= $successMessage ?></div>
<?php endif; ?>

<div class="box">
  <h2>Tổng doanh thu</h2>
  <p class="highlight"><?= number_format($total, 0, ',', '.') ?> VND</p>
</div>

<div class="box">
  <h2>Chọn tuần để xem doanh thu</h2>
  <form method="get" style="max-width:300px;margin:auto;text-align:center;">
    <input type="week" name="week" value="<?= htmlspecialchars($selectedWeek) ?>">
    <button type="submit">Xem</button>
  </form>
</div>

<div class="box">
  <h2>Doanh thu từng ngày trong tuần <?= $week ?>/<?= $year ?></h2>
  <table>
    <tr><th>Ngày</th><th>Thứ</th><th>Doanh thu (VND)</th></tr>
    <?php foreach ($weeklyDaysRevenue as $row): ?>
      <tr>
        <td><?= date('d/m/Y', strtotime($row['ngay'])) ?></td>
        <td><?= $row['thu'] ?></td>
        <td><?= number_format($row['doanh_thu'], 0, ',', '.') ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <div class="chart-container">
    <canvas id="weeklyDaysChart"></canvas>
  </div>
</div>

<div class="box">
  <h2>Doanh thu theo tuần trong tháng</h2>
  <table>
    <tr>
      <th>Tuần</th>
      <th>Tháng</th>
      <th>Năm</th>
      <th>Doanh thu (VND)</th>
    </tr>
    <?php foreach ($weeklyRevenue as $row): ?>
      <tr>
        <td><?= $row['tuan'] ?></td>
        <td><?= $row['thang'] ?></td>
        <td><?= $row['nam'] ?></td>
        <td><?= number_format($row['doanh_thu'], 0, ',', '.') ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <div class="chart-container">
    <canvas id="weeklyChart"></canvas>
  </div>
</div>

<script>
// Biểu đồ theo ngày trong tuần
const weeklyDaysLabels = <?= json_encode(array_map(function($r){ 
  return date('D d/m', strtotime($r['ngay'])); 
}, $weeklyDaysRevenue)) ?>;
const weeklyDaysData   = <?= json_encode(array_map('intval', array_column($weeklyDaysRevenue, 'doanh_thu'))) ?>;

new Chart(document.getElementById('weeklyDaysChart'), {
  type: 'bar',
  data: {
    labels: weeklyDaysLabels,
    datasets: [{
      label: 'Doanh thu từng ngày trong tuần (VND)',
      data: weeklyDaysData,
      backgroundColor: 'rgba(0, 123, 255, 0.6)',
      borderColor: 'rgba(0, 123, 255, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: value => value.toLocaleString() + ' VND'
        }
      }
    }
  }
});

// Biểu đồ theo tuần trong tháng
const weeklyLabels = <?= json_encode(array_map(function($r){ 
  return 'Tuần ' . $r['tuan'] . '/' . $r['thang'] . '/' . $r['nam']; 
}, $weeklyRevenue)) ?>;
const weeklyData   = <?= json_encode(array_map('intval', array_column($weeklyRevenue, 'doanh_thu'))) ?>;

new Chart(document.getElementById('weeklyChart'), {
  type: 'line',
  data: {
    labels: weeklyLabels,
    datasets: [{
      label: 'Doanh thu theo tuần (VND)',
      data: weeklyData,
      borderColor: 'rgba(153, 102, 255, 1)',
      backgroundColor: 'rgba(153, 102, 255, 0.2)',
      fill: true,
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { 
        beginAtZero: true, 
        ticks: { callback: v => v.toLocaleString() + ' VND' } 
      }
    }
  }
});
</script>
</body>
</html>
