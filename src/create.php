<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/database.php';

// 認証チェック
checkAuth();

// POSTリクエストのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['flash_message'] = '不正なメソッドです。';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// CSRFチェック
// require_once 'includes/csrf_token.php';
// verify_csrf_token();

try {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $user_id = getCurrentUserId();

    // 入力値のバリデーション
    if (empty($title) || empty($body)) {
        throw new Exception('タイトルと本文は必須です。');
    }

    if (mb_strlen($title) > 100) {
        throw new Exception('タイトルは100文字以内で入力してください。');
    }

    if (mb_strlen($body) > 5000) {
        throw new Exception('本文は5000文字以内で入力してください。');
    }

    // データベースに保存
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("INSERT INTO posts (title, body, user_id, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$title, $body, $user_id]);

    $_SESSION['flash_message'] = '投稿を作成しました。';
    $_SESSION['flash_type'] = 'success';

} catch (Exception $e) {
    $_SESSION['flash_message'] = $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
    header('Location: new.php');
    exit;
}

header('Location: index.php');
exit;