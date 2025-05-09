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

// POSTリクエスト以外は許可しない
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

// CSRFトークンの検証
// デバッグ情報: POSTとSESSIONのトークンを確認
error_log('POST token: ' . (isset($_POST['csrf_token']) ? $_POST['csrf_token'] : 'not set'));
error_log('SESSION token: ' . (isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : 'not set'));

if (!verify_csrf_token()) {
    $_SESSION['flash_message'] = '不正なリクエストです。CSRFトークンが無効です。';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /');
    exit;
}

// 投稿IDの検証
$post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$post_id) {
    $_SESSION['flash_message'] = '不正なアクセスです。';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /');
    exit;
}

// フォームデータの取得
$title = trim($_POST['title'] ?? '');
$body = trim($_POST['body'] ?? '');

// バリデーション
$errors = [];

if (empty($title)) {
    $errors['title'] = 'タイトルを入力してください。';
} elseif (mb_strlen($title) > 100) {
    $errors['title'] = 'タイトルは100文字以内で入力してください。';
}

if (empty($body)) {
    $errors['body'] = '本文を入力してください。';
} elseif (mb_strlen($body) > 5000) {
    $errors['body'] = '本文は5000文字以内で入力してください。';
}

// エラーがある場合は入力フォームに戻る
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = [
        'title' => $title,
        'body' => $body
    ];
    header("Location: /edit?id={$post_id}");
    exit;
}

try {
    // データベース接続
    $pdo = Database::getInstance()->getConnection();
    
    // 投稿が存在するか確認
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    
    if (!$post) {
        $_SESSION['flash_message'] = '投稿が見つかりません。';
        $_SESSION['flash_type'] = 'danger';
        header('Location: /');
        exit;
    }
    
    // 自分の投稿かどうかをチェック
    if ($post['user_id'] != getCurrentUserId()) {
        $_SESSION['flash_message'] = '編集権限がありません。';
        $_SESSION['flash_type'] = 'danger';
        header('Location: /');
        exit;
    }
    
    // 投稿の更新
    $stmt = $pdo->prepare("UPDATE posts SET title = ?, body = ?, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$title, $body, $post_id]);
    
    if ($result) {
        $_SESSION['flash_message'] = '投稿を更新しました。';
        $_SESSION['flash_type'] = 'success';
        header('Location: /');
    } else {
        throw new Exception('投稿の更新に失敗しました。');
    }
    
} catch (Exception $e) {
    $_SESSION['flash_message'] = '投稿の更新に失敗しました。';
    $_SESSION['flash_type'] = 'danger';
    $_SESSION['form_data'] = [
        'title' => $title,
        'body' => $body
    ];
    header("Location: /edit?id={$post_id}");
    exit;
} 