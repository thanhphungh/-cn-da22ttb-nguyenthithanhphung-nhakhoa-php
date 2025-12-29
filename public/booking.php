<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Chỉ cho phép user/patient đặt lịch, admin chỉ quản lý
if (!in_array($_SESSION['role'], ['user','patient','admin'])) {
    echo "Bạn không có quyền đặt lịch.";
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}

$message = "";

// Nếu form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? null;
    $doctor_id  = $_POST['doctor_id'] ?? null;
    $date       = $_POST['date'] ?? null;
    $time       = $_POST['time'] ?? null;
    $note       = $_POST['note'] ?? '';

    if ($service_id && $doctor_id && $date && $time) {
        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, service_id, date, time, note) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $doctor_id, $service_id, $date, $time, $note]);
            $message = "<span style='color:green;'>Đặt lịch thành công! Vui lòng chờ xác nhận.</span>";
        } catch (PDOException $e) {
            $message = "<span style='color:red;'>Lỗi khi đặt lịch: " . htmlspecialchars($e->getMessage()) . "</span>";
        }
    } else {
        $message = "<span style='color:red;'>Vui lòng nhập đầy đủ thông tin.</span>";
    }
}

// Lấy danh sách dịch vụ
$services = $pdo->query("SELECT id, name, description, price, image FROM services")->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách bác sĩ
$doctors = $pdo->query("SELECT id, name FROM doctors")->fetchAll(PDO::FETCH_ASSOC);

// Nếu có service_id từ URL thì lấy dịch vụ đó
$selectedService = null;
if (isset($_GET['service_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['service_id']]);
    $selectedService = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đặt lịch khám - Phòng khám ABC</title>
  <link rel="stylesheet" href="style_client.css">
  <style>
    .form-box { width: 420px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .form-box h2 { text-align: center; margin-bottom: 20px; }
    .form-box label { display: block; margin-top: 10px; }
    .form-box input, .form-box select, .form-box textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px; }
    .form-box button { margin-top: 15px; width: 100%; padding: 12px; background: #4CAF50; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
    .form-box button:hover { background: #45a049; }
    .message { text-align: center; margin-top: 10px; }
    header {
      background: #007BFF;
      color: white;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .logo { font-size: 20px; font-weight: bold; }
    nav a {
      color: white;
      margin: 0 10px;
      text-decoration: none;
      font-weight: 500;
    }
    nav a:hover { text-decoration: underline; }
    .user-icon {
      display: flex;
      align-items: center;
      gap: 8px;
      position: relative;
      cursor: pointer;
    }
    .user-icon img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 2px solid white;
    }
    .dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 40px;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 6px;
      min-width: 150px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      z-index: 10;
    }
    .dropdown ul { list-style: none; margin: 0; padding: 0; }
    .dropdown li { padding: 10px; }
    .dropdown li a {
      text-decoration: none;
      color: #333;
      display: block;
    }
    .dropdown li a:hover { background: #f0f0f0; }
    .user-icon:hover .dropdown { display: block; }

    .banner {
      text-align: center;
      padding: 50px 20px;
      background: #eafbea;
    }
    .banner h1 {
      font-size: 28px;
      margin-bottom: 20px;
    }
    .banner .btn {
      background: #007BFF;
      color: white;
      padding: 12px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
    }
    .banner .btn:hover { background: #0056b3; }

    .posts {
      width: 80%;
      margin: 30px auto;
    }
    .post {
      background: #fff;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .post h2 { margin: 0; color: #333; }
    .post .date {
      font-size: 12px;
      color: gray;
      margin-bottom: 10px;
    }
    .post img {
      max-width: 100%;
      border-radius: 6px;
      margin-top: 10px;
    }

    footer {
      text-align: center;
      padding: 15px;
      background: #f0f0f0;
      margin-top: 30px;
    }
  </style>
</head>
<body>
<header>
  <div class="logo">Phòng khám ABC</div>
  <nav>
    <a href="index.php">Trang chủ</a>
    <a href="about.php">Giới thiệu</a>
    <a href="dichvu.php" class="active">Dịch vụ</a>
    <a href="booking.php">Đặt lịch khám</a>
    <a href="lookup.php">Tra cứu lịch hẹn</a>
    <a href="doctors.php">Bác sĩ</a>
    <a href="contact.php">Liên hệ</a>
  </nav>
  <div class="user-icon">
    <img src="uploads/<?php echo $_SESSION['avatar'] ?? 'default-user.png'; ?>" alt="User" />
    <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
    <div class="dropdown">
      <ul>
        <li><a href="profile.php">Hồ sơ cá nhân</a></li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <li><a href="dashboard.php">Trang quản trị</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Đăng xuất</a></li>
      </ul>
    </div>
  </div>
</header>
<div class="form-box">
  <h2>Đặt lịch khám</h2>
  <?php if ($message): ?>
    <p class="message"><?= $message ?></p>
  <?php endif; ?>

  <?php if ($selectedService): ?>
    <div class="service-info">
      <?php if (!empty($selectedService['image'])): ?>
        <img src="<?= htmlspecialchars($selectedService['image']) ?>" alt="<?= htmlspecialchars($selectedService['name']) ?>">
      <?php endif; ?>
      <h3><?= htmlspecialchars($selectedService['name']) ?></h3>
      <p><?= nl2br(htmlspecialchars($selectedService['description'])) ?></p>
      <div class="price"><?= number_format($selectedService['price'], 0, ',', '.') ?> VND</div>
    </div>
  <?php endif; ?>

  <form method="post">
    <label>Dịch vụ:</label>
    <select name="service_id" required>
      <option value="">-- Chọn dịch vụ --</option>
      <?php foreach ($services as $s): ?>
        <option value="<?= $s['id'] ?>" 
          <?= ($selectedService && $selectedService['id'] == $s['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($s['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Bác sĩ:</label>
    <select name="doctor_id" required>
      <option value="">-- Chọn bác sĩ --</option>
      <?php foreach ($doctors as $d): ?>
        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Ngày khám:</label>
    <input type="date" name="date" min="<?= date('Y-m-d') ?>" required>

    <label>Giờ khám:</label>
    <input type="time" name="time" required>

    <label>Ghi chú:</label>
    <textarea name="note" rows="3"></textarea>

    <button type="submit">Đặt lịch</button>
  </form>
</div>

<footer>
  <p>&copy; 2025 Phòng khám ABC. All rights reserved.</p>
</footer>
</body>
</html>
