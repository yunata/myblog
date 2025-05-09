<?php
/**
 * CSRFトークン関連の機能
 */

/**
 * CSRFトークンを生成
 * セッションにトークンがなければ新規に生成する
 * @return string CSRFトークン
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRFトークン入力フィールドを出力
 * フォームにCSRFトークンを挿入するためのユーティリティ関数
 */
function csrf_token_field() {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * CSRFトークンが有効かどうかを検証
 * @return bool トークンが有効ならtrue、無効ならfalse
 */
function verify_csrf_token() {
    // POSTリクエスト以外は検証しない
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true;
    }
    
    // セッションとPOSTパラメータの両方にトークンが存在し、一致するか検証
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token'])) {
        error_log('CSRF verification failed: token missing');
        return false;
    }
    
    $valid = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    if (!$valid) {
        error_log('CSRF verification failed: tokens do not match');
    }
    
    return $valid;
}