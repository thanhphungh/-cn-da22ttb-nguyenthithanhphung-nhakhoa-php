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

$id = $_GET['id'] ?? null;

if ($id) {
    // Xóa chi tiết dịch vụ trước
    $stmt = $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
    $stmt->execute([$id]);

    // Xóa hóa đơn
    $stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
    $stmt->execute([$id]);
}

// Quay lại trang danh sách hóa đơn
header("Location: invoice.php");
exit;
