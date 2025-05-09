<?php
require_once INCLUDES_DIR . '/auth.php';
require_once INCLUDES_DIR . '/database.php';

// 認証チェック - ログインしていない場合はログインページにリダイレクト
if (!isLoggedIn()) {
    $_SESSION['flash_message'] = 'ログインが必要です。';
    $_SESSION['flash_type'] = 'warning';
    header('Location: /login');
    exit;
}

// 投稿IDの取得と検証
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$post_id) {
    $_SESSION['flash_message'] = '不正なアクセスです。';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /');
    exit;
}

try {
    // データベース接続
    $pdo = Database::getInstance()->getConnection();
    
    // 投稿データの取得
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    
    // 投稿が存在しない場合
    if (!$post) {
        $_SESSION['flash_message'] = '投稿が見つかりません。';
        $_SESSION['flash_type'] = 'danger';
        header('Location: /');
        exit;
    }
    
    // 自分の投稿かどうかをチェック
    if ($post['user_id'] != getCurrentUserId()) {
        $_SESSION['flash_message'] = '削除権限がありません。';
        $_SESSION['flash_type'] = 'danger';
        header('Location: /');
        exit;
    }
    
    // 投稿の削除
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $result = $stmt->execute([$post_id]);
    
    if ($result) {
        $_SESSION['flash_message'] = '投稿を削除しました。';
        $_SESSION['flash_type'] = 'success';
    } else {
        throw new Exception('投稿の削除に失敗しました。');
    }
    
} catch (Exception $e) {
    $_SESSION['flash_message'] = '削除処理中にエラーが発生しました。';
    $_SESSION['flash_type'] = 'danger';
}

// 一覧ページにリダイレクト
header('Location: /');
exit; 