<?php
$host = 'db'; // Dockerのサービス名（localhostじゃないよ！）
$db   = 'blog';
$user = 'user';
$pass = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // 成功したら何か表示
    echo "✅ Connected to DB!";
} catch (PDOException $e) {
    echo "❌ DB connection failed: " . $e->getMessage();
}