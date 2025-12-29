<?php
// actions/local_login.php
require __DIR__ . '/../includes/db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login.php'); exit;
}
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// tìm user
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email'=>$email]);
$user = $stmt->fetch();
if (!$user) {
    $_SESSION['login_error'] = 'Không tìm thấy tài khoản';
    header('Location: ../public/login.php');
    exit;
}
// nếu bạn lưu password hashed (bcrypt)
if (!empty($user['password'])) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']
        ];
        header('Location: ../public/dashboard.php'); exit;
    } else {
        $_SESSION['login_error'] = 'Sai mật khẩu';
        header('Location: ../public/login.php'); exit;
    }
} else {
    // user tồn tại nhưng không có password (OAuth only)
    $_SESSION['login_error'] = 'Tài khoản này dùng đăng nhập bằng Google';
    header('Location: ../public/login.php'); exit;
}
