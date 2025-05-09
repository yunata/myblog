<?php
// セッションが既に開始されていることを確認
require_once INCLUDES_DIR . '/auth.php';
require_once INCLUDES_DIR . '/csrf_token.php';

// すでにログインしている場合はindexにリダイレクト
if (isLoggedIn()) {
    header('Location: /');
    exit;
}

$pageTitle = "ログイン";
include INCLUDES_DIR . '/header.php';
?>

<main class="container">
    <h1 class="page-title">ログイン</h1>
    
    <form action="/login_check" method="POST" class="auth-form">
        <?php csrf_token_field(); ?>
        
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">ログイン</button>
        </div>
        
        <div class="auth-links">
            <a href="/register">新規ユーザー登録はこちら</a>
        </div>
    </form>
</main>

<?php include INCLUDES_DIR . '/footer.php'; ?> 