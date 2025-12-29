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
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

$patient_id = $_POST['patient_id'] ?? null;
$services   = $_POST['services'] ?? [];

if ($patient_id && !empty($services)) {
    $total = 0;

    // Tính tổng tiền
    foreach ($services as $s) {
        if (!isset($s['id'], $s['price'], $s['quantity'])) {
            continue; // bỏ qua nếu thiếu dữ liệu
        }

        $service_id = (int)$s['id'];
        $price      = (float)$s['price'];
        $quantity   = (int)$s['quantity'];

        if ($service_id > 0 && $quantity > 0 && $price >= 0) {
            $total += $price * $quantity;
        }
    }

    // Nếu tổng = 0 thì coi như lỗi
    if ($total <= 0) {
        header("Location: invoice.php?error=1");
        exit;
    }

    // Lưu hóa đơn
    $stmt = $pdo->prepare("INSERT INTO invoices (patient_id, total, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$patient_id, $total]);
    $invoice_id = $pdo->lastInsertId();

    // Lưu chi tiết dịch vụ
    foreach ($services as $s) {
        if (!isset($s['id'], $s['price'], $s['quantity'])) {
            continue;
        }

        $service_id = (int)$s['id'];
        $price      = (float)$s['price'];
        $quantity   = (int)$s['quantity'];

        if ($service_id > 0 && $quantity > 0 && $price >= 0) {
            $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, service_id, quantity, price)
                                   VALUES (?, ?, ?, ?)");
            $stmt->execute([$invoice_id, $service_id, $quantity, $price]);
        }
    }

    header("Location: invoice.php?success=1&invoice_id=" . $invoice_id);
    exit;
} else {
    header("Location: invoice.php?error=1");
    exit;
}
