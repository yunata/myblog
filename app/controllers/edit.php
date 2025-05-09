<?php
require_once INCLUDES_DIR . '/auth.php';
require_once INCLUDES_DIR . '/database.php';
require_once INCLUDES_DIR . '/csrf_token.php';

// 認証チェック - ログインしていない場合はログインページにリダイレクト
if (!isLoggedIn()) {
    $_SESSION['flash_message'] = 'ログインが必要です。';
    $_SESSION['flash_type'] = 'warning';
    header('Location: /login');
    exit;
}

// 投稿IDの取得
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$post_id) {
    $_SESSION['flash_message'] = '不正なアクセスです。';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /');
    exit;
}

try {
    // データベース接続
    $pdo = Database::getInstance()->getConnection();
    
    // 投稿データの取得
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    
    // 投稿が存在しない場合
    if (!$post) {
        $_SESSION['flash_message'] = '投稿が見つかりません。';
        $_SESSION['flash_type'] = 'danger';
        header('Location: /');
        exit;
    }
    
    // 自分の投稿かどうかをチェック
    if ($post['user_id'] != getCurrentUserId()) {
        $_SESSION['flash_message'] = '編集権限がありません。';
        $_SESSION['flash_type'] = 'danger';
        header('Location: /');
        exit;
    }
    
    // セッションにフォームデータがある場合は、それを使用
    if (isset($_SESSION['form_data'])) {
        $post = array_merge($post, $_SESSION['form_data']);
        unset($_SESSION['form_data']);
    }
    
    // ヘッダー部分の読み込み
    $pageTitle = "投稿の編集";
    include INCLUDES_DIR . '/header.php';
    
    // デバッグ情報: CSRFトークンの状態を確認
    error_log('Edit page SESSION token: ' . (isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : 'not set'));
    
} catch (Exception $e) {
    $_SESSION['flash_message'] = 'データの取得に失敗しました。';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /');
    exit;
}
?>

<main class="container">
    <h1 class="page-title">投稿の編集</h1>
    
    <form action="/update" method="POST" class="post-form" novalidate>
        <?php csrf_token_field(); ?>
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        
        <div class="form-group">
            <label for="title" class="required">タイトル:</label>
            <input type="text" 
                   id="title" 
                   name="title" 
                   required 
                   maxlength="100"
                   class="form-control"
                   value="<?php echo htmlspecialchars($post['title']); ?>">
        </div>

        <div class="form-group">
            <label for="body" class="required">本文:</label>
            <textarea id="body" 
                      name="body" 
                      rows="10" 
                      required 
                      class="form-control"
                      maxlength="5000"><?php echo htmlspecialchars($post['body']); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">更新する</button>
            <a href="/" class="btn btn-secondary">戻る</a>
        </div>
    </form>
</main>

<?php include INCLUDES_DIR . '/footer.php'; ?> 