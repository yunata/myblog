<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/database.php';

// 認証チェック
checkAuth();

try {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('無効な投稿IDです。');
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
        throw new Exception('この投稿を削除する権限がありません。');
    }

    // 投稿の削除
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, getCurrentUserId()]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash_message'] = '投稿を削除しました。';
        $_SESSION['flash_type'] = 'success';
    } else {
        throw new Exception('投稿の削除に失敗しました。');
    }

} catch (Exception $e) {
    $_SESSION['flash_message'] = $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

header('Location: index.php');
exit;