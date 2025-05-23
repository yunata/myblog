<?php
// 必要に応じて認証機能を読み込む
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/auth.php';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'ブログ'); ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <nav class="nav-container">
            <div class="nav-brand">
                <a href="/">マイブログ</a>
            </div>
            <div class="nav-menu">
                <?php if (isLoggedIn()): ?>
                    <a href="/">ホーム</a>
                    <a href="/new">新規投稿</a>
                    <a href="/logout">ログアウト</a>
                <?php else: ?>
                    <a href="/login">ログイン</a>
                    <a href="/register">新規登録</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <div class="flash-messages">
        <?php
        if (isset($_SESSION['flash_message'])) {
            echo '<div class="alert alert-' . ($_SESSION['flash_type'] ?? 'info') . '">';
            echo htmlspecialchars($_SESSION['flash_message']);
            echo '</div>';
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        }
        ?>
    </div>
</body>
</html> 