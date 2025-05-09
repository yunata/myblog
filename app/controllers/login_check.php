<?php
// 必要なファイルの読み込み
require_once INCLUDES_DIR . '/auth.php';
require_once INCLUDES_DIR . '/database.php';
require_once INCLUDES_DIR . '/csrf_token.php';

// POSTリクエストのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['flash_message'] = '不正なメソッドです。';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /login');
    exit;
}

// CSRFトークンを検証
verify_csrf_token();

try {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (!$email || empty($password)) {
        throw new Exception('メールアドレスとパスワードを入力してください。');
    }
    
    // データベース接続
    $pdo = Database::getInstance()->getConnection();
    
    // ユーザーを検索
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception('メールアドレスまたはパスワードが正しくありません。');
    }
    
    // セッションにユーザー情報を保存
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    
    $_SESSION['flash_message'] = 'ログインしました。';
    $_SESSION['flash_type'] = 'success';
    
    // リダイレクト先の処理
    $redirect = '/';
    if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
        $redirect = $_GET['redirect'];
    }
    
    header("Location: $redirect");
    exit;
    
} catch (Exception $e) {
    $_SESSION['flash_message'] = $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
    header('Location: /login');
    exit;
} 