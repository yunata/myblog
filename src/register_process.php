<?php
ob_start();

require_once 'includes/session.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/csrf_token.php';

// POSTリクエストのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['flash_message'] = '不正なメソッドです。';
    $_SESSION['flash_type'] = 'danger';
    header('Location: register.php');
    exit;
}

// CSRFチェック
require_once 'includes/csrf_token.php';

try {
    $username = trim($_POST['username'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // 入力値のバリデーション
    if (empty($username) || !$email || empty($password)) {
        throw new Exception('すべての項目を入力してください。');
    }
    
    if (mb_strlen($username) > 50) {
        throw new Exception('ユーザー名は50文字以内で入力してください。');
    }
    
    if (mb_strlen($password) < 8) {
        throw new Exception('パスワードは8文字以上で入力してください。');
    }
    
    if ($password !== $password_confirm) {
        throw new Exception('パスワードが一致しません。');
    }
    
    $pdo = Database::getInstance()->getConnection();
    
    // ユーザー名の重複チェック
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        throw new Exception('このユーザー名はすでに使用されています。');
    }
    
    // メールアドレスの重複チェック
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('このメールアドレスはすでに登録されています。');
    }
    
    // パスワードのハッシュ化
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // ユーザー登録
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$username, $email, $password_hash]);
    
    $user_id = $pdo->lastInsertId();
    
    // セッションに登録
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    
    $_SESSION['flash_message'] = 'ユーザー登録が完了しました。';
    $_SESSION['flash_type'] = 'success';
    
    header('Location: index.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['flash_message'] = $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
    header('Location: register.php');
    exit;
}