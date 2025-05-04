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
    
    // 投稿の取得
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if (!$post) {
        throw new Exception('投稿が見つかりません。');
    }

    if ($post['user_id'] !== getCurrentUserId()) {
        throw new Exception('この投稿を編集する権限がありません。');
    }

    $pageTitle = "投稿の編集";
    include 'includes/header.php';
} catch (Exception $e) {
    $_SESSION['flash_message'] = $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}
?>

<main class="container">
    <h1 class="page-title">📝 投稿を編集</h1>
    
    <form action="update.php" method="POST" class="post-form">
        <!-- <?php include 'includes/csrf_token.php'; ?> -->
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($post['id']); ?>">
        
        <div class="form-group">
            <label for="title" class="required">タイトル:</label>
            <input type="text" 
                   id="title" 
                   name="title" 
                   class="form-control"
                   required 
                   maxlength="100"
                   value="<?php echo htmlspecialchars($post['title']); ?>">
        </div>

        <div class="form-group">
            <label for="body" class="required">本文:</label>
            <textarea id="body" 
                      name="body" 
                      class="form-control"
                      rows="5" 
                      required 
                      maxlength="5000"><?php echo htmlspecialchars($post['body']); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">更新する</button>
            <a href="index.php" class="btn btn-secondary">戻る</a>
        </div>
    </form>
</main>

<?php include 'includes/footer.php'; ?>