<?php
session_start();
$pdo = new PDO("mysql:host=db;dbname=blog;charset=utf8mb4", "user", "password");

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

// デバッグ情報を先に出力
// var_dump($user);
// var_dump($password);
// if ($user) {
//     var_dump($user['password']);
// }

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    header("Location: index.php");
    exit;
} else {
    echo "ログイン失敗！";
    echo "<br>理由: ";
    if (!$user) {
        echo "ユーザーが見つかりません。";
    } else {
        echo "パスワードが一致しません。";
    }
}