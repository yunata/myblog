<?php
require_once INCLUDES_DIR . '/auth.php';

// ログアウト処理
logout();

$_SESSION['flash_message'] = 'ログアウトしました。';
$_SESSION['flash_type'] = 'success';

// ホームページにリダイレクト
header('Location: /');
exit; 