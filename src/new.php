<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';

// 認証チェック
checkAuth();

// ヘッダー部分の読み込み
$pageTitle = "新規投稿";
include 'includes/header.php';
?>

<main class="container">
    <h1 class="page-title">📝 新規投稿フォーム</h1>
    
    <form action="create.php" method="POST" class="post-form" novalidate>
        <?php include 'includes/csrf_token.php'; ?>
        
        <div class="form-group">
            <label for="title" class="required">タイトル:</label>
            <input type="text" 
                   id="title" 
                   name="title" 
                   required 
                   maxlength="100"
                   class="form-control"
                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="body" class="required">本文:</label>
            <textarea id="body" 
                      name="body" 
                      rows="5" 
                      required 
                      class="form-control"
                      maxlength="5000"><?php echo htmlspecialchars($_POST['body'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">投稿する</button>
            <a href="index.php" class="btn btn-secondary">戻る</a>
        </div>
    </form>
</main>

<?php include 'includes/footer.php'; ?>