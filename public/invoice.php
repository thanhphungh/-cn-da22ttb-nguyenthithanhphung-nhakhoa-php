<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$users = $pdo->query("SELECT id, name FROM users")->fetchAll(PDO::FETCH_ASSOC);
$services = $pdo->query("SELECT id, name, price FROM services")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Tạo hóa đơn</title>
 <link rel="stylesheet" href="style.css">
<style>
    /* Container cho form tạo hóa đơn */
.container {
  max-width: 700px;
  margin: 40px auto;
  background: #fff;
  padding: 25px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Tiêu đề */
.container h1 {
  text-align: center;
  color: #0066cc;
  margin-bottom: 20px;
  font-size: 26px;
}

/* Thông báo thành công / lỗi */
.container .msg-success {
  color: #28a745;
  text-align: center;
  font-weight: bold;
  margin-bottom: 15px;
}
.container .msg-error {
  color: #c0392b;
  text-align: center;
  font-weight: bold;
  margin-bottom: 15px;
}

/* Form */
.container form label {
  display: block;
  margin-top: 15px;
  font-weight: bold;
  color: #333;
}
.container form select,
.container form input[type="text"] {
  width: 100%;
  padding: 10px;
  margin-top: 6px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
}

/* Suggestions box */
#suggestions {
  margin-top: 8px;
  border: 1px solid #ddd;
  border-radius: 6px;
  max-height: 200px;
  overflow-y: auto;
}
#suggestions div {
  padding: 8px;
  cursor: pointer;
}
#suggestions div:hover {
  background: #e6f2ff;
}

/* Dịch vụ đã chọn */
#selected-services .service-item {
  background: #f9f9f9;
  padding: 8px;
  border-radius: 6px;
  margin-top: 6px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Tổng tiền */
.total {
  font-size: 18px;
  font-weight: bold;
  color: #28a745;
  margin-top: 15px;
  text-align: right;
}

/* Nút tạo hóa đơn */
.container form button[type="submit"] {
  margin-top: 20px;
  width: 100%;
  padding: 12px;
  background: #28a745;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
}
.container form button[type="submit"]:hover {
  background: #218838;
}

/* Nút in hóa đơn */
.container a button {
  padding: 10px 20px;
  background: #007BFF;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.container a button:hover {
  background: #0056b3;
}

</style>
</head>
<body>
<header>
  <h1>Hóa đơn</h1>
  <a href="logout.php" class="logout">Đăng xuất</a>
</header>
<nav>
  <a href="users.php">Người dùng</a>
  <a href="services.php">Dịch vụ</a>
  <a href="appointments.php">Lịch hẹn</a>
  <a href="patients.php">Quản lí khách hàng</a>
  <a href="posts.php">Quản lí bài đăng</a>
  <a href="invoice.php" class="active">Hóa đơn</a>
  <a href="revenue.php">Doanh thu</a>
  <a href="quanlybacsi.php">Quản lí bác sĩ</a>
  <a href="tiepnhanlienhe.php">Tiếp nhận liên hệ</a>
  <a href="index.php">Trang khách hàng</a>
</nav>
<div class="container">
  <h1>Tạo hóa đơn mới</h1>
  <?php if (isset($_GET['success']) && isset($_GET['invoice_id'])): ?>
    <div style="text-align:center; margin-bottom:10px;">
      <p style="color:green;">✅ Hóa đơn đã được lưu thành công!</p>
      <a href="print_invoice.php?invoice_id=<?= htmlspecialchars($_GET['invoice_id']) ?>" target="_blank">
        <button style="background:#007BFF;">In hóa đơn</button>
      </a>
    </div>
  <?php elseif (isset($_GET['error'])): ?>
    <p style="color:red;text-align:center;">❌ Vui lòng chọn người dùng và ít nhất một dịch vụ.</p>
  <?php endif; ?>

  <form method="post" action="save_invoice.php">
    <label>Chọn người dùng:</label>
    <select name="patient_id" required>
      <option value="">-- Chọn user --</option>
      <?php foreach ($users as $u): ?>
        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (ID: <?= $u['id'] ?>)</option>
      <?php endforeach; ?>
    </select>

    <label>Thêm dịch vụ:</label>
    <input type="text" id="serviceSearch" placeholder="Nhập tên dịch vụ...">
    <div id="suggestions"></div>

    <div id="selected-services"></div>
    <p class="total">Tổng: <span id="total">0</span> VND</p>

    <button type="submit">Tạo hóa đơn</button>
  </form>

  <hr>
  <h2>Danh sách hóa đơn</h2>
  <table border="1" width="100%" style="margin-top:10px; border-collapse:collapse;">
    <tr style="background:#f0f0f0;">
      <th>ID</th>
      <th>Bệnh nhân</th>
      <th>Ngày tạo</th>
      <th>Tổng tiền</th>
      <th>Thao tác</th>
    </tr>
    <?php
    $invoices = $pdo->query("
      SELECT i.id, i.total, i.created_at, u.name AS patient_name
      FROM invoices i
      LEFT JOIN users u ON i.patient_id = u.id
      ORDER BY i.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($invoices as $inv): ?>
      <tr>
        <td><?= $inv['id'] ?></td>
        <td><?= htmlspecialchars($inv['patient_name']) ?></td>
        <td><?= $inv['created_at'] ?></td>
        <td><?= number_format($inv['total'], 0, ',', '.') ?> đ</td>
        <td>
          <a href="print_invoice.php?invoice_id=<?= $inv['id'] ?>" target="_blank">
            <button style="background:#007BFF; color:#fff;">🖨️ In</button>
          </a>
          <a href="delete_invoice.php?id=<?= $inv['id'] ?>" onclick="return confirm('Xóa hóa đơn này?')">
            <button style="background:#dc3545; color:#fff;">🗑️ Xóa</button>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<script>
const services = <?= json_encode($services) ?>;
const searchInput = document.getElementById('serviceSearch');
const suggestions = document.getElementById('suggestions');
const selectedServices = document.getElementById('selected-services');
const totalEl = document.getElementById('total');
let total = 0;

searchInput.addEventListener('input', () => {
  const query = searchInput.value.toLowerCase();
  suggestions.innerHTML = '';
  if (query.length > 0) {
    const matches = services.filter(s => s.name.toLowerCase().includes(query));
    matches.forEach(s => {
      const div = document.createElement('div');
      div.textContent = s.name + ' - ' + Number(s.price).toLocaleString() + ' VND';
      div.onclick = () => addService(s);
      suggestions.appendChild(div);
    });
  }
});

function addService(service) {
  const index = selectedServices.children.length;
  const div = document.createElement('div');
  div.className = 'service-item';
  div.textContent = service.name + ' - ' + Number(service.price).toLocaleString() + ' VND';

  const inputId = document.createElement('input');
  inputId.type = 'hidden';
  inputId.name = `services[${index}][id]`;
  inputId.value = service.id;
  div.appendChild(inputId);

  const inputPrice = document.createElement('input');
  inputPrice.type = 'hidden';
  inputPrice.name = `services[${index}][price]`;
  inputPrice.value = service.price;
  div.appendChild(inputPrice);

  const inputQty = document.createElement('input');
  inputQty.type = 'number';
  inputQty.name = `services[${index}][quantity]`;
  inputQty.value = 1;
  inputQty.min = 1;
  inputQty.style.marginLeft = '10px';
  inputQty.onchange = () => updateTotal();
  div.appendChild(inputQty);

  selectedServices.appendChild(div);
  updateTotal();

  searchInput.value = '';
  suggestions.innerHTML = '';
}

function updateTotal() {
  total = 0;
  const items = selectedServices.querySelectorAll('.service-item');
  items.forEach(item => {
    const price = parseFloat(item.querySelector('input[name$="[price]"]').value);
    const qty = parseInt(item.querySelector('input[name$="[quantity]"]').value);
    total += price * qty;
  });
  totalEl.textContent = total.toLocaleString();
}
</script>
</body>
</html>
