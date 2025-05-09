<?php
// 以下の設定はヘッダが送信される前に設定する必要があります
// session_start()前に行う必要があるため、この設定は
// 他のファイルから利用する場合は役に立ちません

if (session_status() === PHP_SESSION_NONE) {
    // セッションの設定
    @ini_set('session.cookie_httponly', 1);
    @ini_set('session.cookie_secure', 1);
    @ini_set('session.cookie_samesite', 'Strict');
    @ini_set('session.gc_maxlifetime', 3600); // 1時間
    
    session_start();
}

// セッションIDの再生成（セッションハイジャック対策）
if (!isset($_SESSION['last_regeneration']) || 
    time() - $_SESSION['last_regeneration'] >= 300) {
    @session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} 