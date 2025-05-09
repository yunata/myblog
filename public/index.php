<?php
// アプリケーションのルートディレクトリを定義
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// 共通関数・クラスのディレクトリを定義
if (!defined('INCLUDES_DIR')) {
    define('INCLUDES_DIR', APP_ROOT . '/app/includes');
}

// ブートストラップファイルの読み込み
require_once APP_ROOT . '/bootstrap.php';

// リクエストURIを解析
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// ベースパスを取得（サブディレクトリにインストールされている場合に対応）
$base_path = dirname($script_name);
if ($base_path != '/' && $base_path != '\\') {
    $request_uri = substr($request_uri, strlen($base_path));
}

// クエリ文字列を削除
$request_uri = parse_url($request_uri, PHP_URL_PATH);

// リクエストに基づいてルーティング
switch ($request_uri) {
    case '/':
    case '/index.php':
    case '/index':
        require APP_ROOT . '/app/controllers/index.php';
        break;
    
    case '/login':
        require APP_ROOT . '/app/controllers/login.php';
        break;
    
    case '/login_check':
        require APP_ROOT . '/app/controllers/login_check.php';
        break;
    
    case '/register':
        require APP_ROOT . '/app/controllers/register.php';
        break;
    
    case '/register_process':
        require APP_ROOT . '/app/controllers/register_process.php';
        break;
    
    case '/logout':
        require APP_ROOT . '/app/controllers/logout.php';
        break;
    
    case '/new':
        require APP_ROOT . '/app/controllers/new.php';
        break;
    
    case '/create':
        require APP_ROOT . '/app/controllers/create.php';
        break;
    
    case '/edit':
        require APP_ROOT . '/app/controllers/edit.php';
        break;
    
    case '/update':
        require APP_ROOT . '/app/controllers/update.php';
        break;
    
    case '/delete':
        require APP_ROOT . '/app/controllers/delete.php';
        break;
    
    default:
        // 404 Not Found
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        break;
} 