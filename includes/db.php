<?php
// Tạo kết nối PDO và trả về
$pdo = new PDO(
    'mysql:host=localhost;dbname=phongnha_db;charset=utf8mb4',
    'root',
    ''
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

return $pdo;
