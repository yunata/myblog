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
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');

    // 入力値のバリデーション
    if (!$id) {
        throw new Exception('無効な投稿IDです。');
    }

    if (empty($title) || empty($body)) {
        throw new Exception('タイトルと本文は必須です。');
    }

    if (mb_strlen($title) > 100) {
        throw new Exception('タイトルは100文字以内で入力してください。');
    }

    if (mb_strlen($body) > 5000) {
        throw new Exception('本文は5000文字以内で入力してください。');
    }

    $pdo = Database::getInstance()->getConnection();

    // 投稿の存在確認と所有者チェック
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if (!$post) {
        throw new Exception('投稿が見つかりません。');
    }

    if ($post['user_id'] !== getCurrentUserId()) {
        throw new Exception('この投稿を更新する権限がありません。');
    }

    // 投稿の更新
    $stmt = $pdo->prepare("UPDATE posts SET title = ?, body = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $body, $id, getCurrentUserId()]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash_message'] = '投稿を更新しました。';
        $_SESSION['flash_type'] = 'success';
    } else {
        throw new Exception('投稿の更新に失敗しました。');
    }

} catch (Exception $e) {
    $_SESSION['flash_message'] = $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
    header("Location: edit.php?id=$id");
    exit;
}

header('Location: index.php');
exit;