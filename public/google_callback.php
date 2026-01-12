<?php
session_start();

$config = require __DIR__ . '/../includes/config.php';
$pdo    = require __DIR__ . '/../includes/db.php';

$google = $config['google'];

if (!isset($_GET['code'])) {
    exit('Missing code');
}

// Đổi code lấy token
$token = fetch('https://oauth2.googleapis.com/token', [
    'code'          => $_GET['code'],
    'client_id'     => $google['client_id'],
    'client_secret' => $google['client_secret'],
    'redirect_uri'  => $google['redirect_uri'],
    'grant_type'    => 'authorization_code'
]);

if (!isset($token['access_token'])) {
    exit('Failed to obtain access token');
}

// Lấy thông tin người dùng
$userinfo = fetchJson('https://www.googleapis.com/oauth2/v3/userinfo', [], $token['access_token']);

if (!is_array($userinfo)) {
    exit('Failed to fetch user info');
}

$email  = $userinfo['email']   ?? '';
$name   = $userinfo['name']    ?? '';
$avatar = $userinfo['picture'] ?? '';

// Kiểm tra user trong DB
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    // Role mặc định là patient
    $stmt = $pdo->prepare("INSERT INTO users (name, email, avatar, role) VALUES (?, ?, ?, 'patient')");
    $stmt->execute([$name, $email, $avatar]);
    $userId = $pdo->lastInsertId();
} else {
    $userId = $user['id'];
}

$_SESSION['user_id'] = $userId;
$_SESSION['user_id'] = $userId;
$_SESSION['name']    = $name;
$_SESSION['email']   = $email;
$_SESSION['avatar']  = $avatar;
$_SESSION['role']    = 'patient';
header('Location: index.php');
exit;


// ===== Helpers =====
function fetch($url, $data)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true);
}

function fetchJson($url, $query, $token)
{
    $ch = curl_init($url . '?' . http_build_query($query));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token]
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true);
}
