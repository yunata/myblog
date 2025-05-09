<?php
require_once INCLUDES_DIR . '/auth.php';
require_once INCLUDES_DIR . '/csrf_token.php';

// 認証チェック - ログインしていない場合はログインページにリダイレクト
if (!isLoggedIn()) {
    $_SESSION['flash_message'] = 'ログインが必要です。';
    $_SESSION['flash_type'] = 'warning';
    header('Location: /login');
    exit;
}

// ヘッダー部分の読み込み
$pageTitle = "新規投稿";
include INCLUDES_DIR . '/header.php';
?>

<main class="container">
    <h1 class="page-title">📝 新規投稿フォーム</h1>
    
    <form action="/create" method="POST" class="post-form" novalidate>
        <?php csrf_token_field(); ?>
        
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
                      rows="10" 
                      required 
                      class="form-control"
                      maxlength="5000"><?php echo htmlspecialchars($_POST['body'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">投稿する</button>
            <a href="/" class="btn btn-secondary">戻る</a>
        </div>
    </form>
</main>

<?php include INCLUDES_DIR . '/footer.php'; ?> 