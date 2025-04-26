<?php
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        // ログインページのURLを取得
        $login_url = 'login.php';
        if (!empty($_SERVER['REQUEST_URI'])) {
            // 現在のページをリダイレクト先として保存
            $login_url .= '?redirect=' . urlencode($_SERVER['REQUEST_URI']);
        }
        header("Location: " . $login_url);
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function logout() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
} 