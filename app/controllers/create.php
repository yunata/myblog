<?php
require_once INCLUDES_DIR . '/auth.php';
require_once INCLUDES_DIR . '/database.php';
require_once INCLUDES_DIR . '/csrf_token.php';

// 認証チェック - ログインしていない場合はログインページにリダイレクト
if (!isLoggedIn()) {
    $_SESSION['flash_message'] = 'ログインが必要です。';
    $_SESSION['flash_type'] = 'warning';
    header('Location: /login');
    exit;
}

// POSTリクエストのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['flash_message'] = '不正なメソッドです。';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /new');
    exit;
}

// CSRFトークンを検証
verify_csrf_token();

try {
    // 投稿データの取得と検証
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $user_id = getCurrentUserId();
    
    // バリデーション
    if (empty($title)) {
        throw new Exception('タイトルを入力してください。');
    }
    
    if (mb_strlen($title) > 100) {
        throw new Exception('タイトルは100文字以内で入力してください。');
    }
    
    if (empty($body)) {
        throw new Exception('本文を入力してください。');
    }
    
    if (mb_strlen($body) > 5000) {
        throw new Exception('本文は5000文字以内で入力してください。');
    }
    
    // データベース接続
    $pdo = Database::getInstance()->getConnection();
    
    // 投稿をデータベースに保存
    $stmt = $pdo->prepare("
        INSERT INTO posts (title, body, user_id, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$title, $body, $user_id]);
    
    $_SESSION['flash_message'] = '投稿が完了しました。';
    $_SESSION['flash_type'] = 'success';
    
    header('Location: /');
    exit;
    
} catch (Exception $e) {
    $_SESSION['flash_message'] = $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
    
    // 入力データをセッションに保存して、フォームに戻る
    $_SESSION['form_data'] = [
        'title' => $title,
        'body' => $body
    ];
    
    header('Location: /new');
    exit;
} 