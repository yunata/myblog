<?php
// セッションが既に開始されていることを確認
require_once INCLUDES_DIR . '/auth.php';
require_once INCLUDES_DIR . '/csrf_token.php';

// すでにログインしている場合はindexにリダイレクト
if (isLoggedIn()) {
    header('Location: /');
    exit;
}

$pageTitle = "新規ユーザー登録";
include INCLUDES_DIR . '/header.php';
?>

<main class="container">
    <h1 class="page-title">新規ユーザー登録</h1>
    
    <form action="/register_process" method="POST" class="auth-form">
        <?php csrf_token_field(); ?>
        
        <div class="form-group">
            <label for="username" class="required">ユーザー名:</label>
            <input type="text" 
                   id="username" 
                   name="username" 
                   required 
                   maxlength="50"
                   class="form-control"
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="email" class="required">メールアドレス:</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   required 
                   maxlength="255"
                   class="form-control"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password" class="required">パスワード:</label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   required 
                   minlength="8"
                   class="form-control">
            <small class="form-text">※8文字以上で入力してください</small>
        </div>
        
        <div class="form-group">
            <label for="password_confirm" class="required">パスワード（確認）:</label>
            <input type="password" 
                   id="password_confirm" 
                   name="password_confirm" 
                   required 
                   minlength="8"
                   class="form-control">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">登録する</button>
            <a href="/login" class="btn btn-secondary">ログインに戻る</a>
        </div>
    </form>
</main>

<?php include INCLUDES_DIR . '/footer.php'; ?> 