<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/database.php';

$pageTitle = "ブログ投稿一覧";
include 'includes/header.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    // ページネーション設定
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    // 総投稿数を取得
    $countStmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $totalPosts = $countStmt->fetchColumn();
    $totalPages = ceil($totalPosts / $perPage);
    
    // 投稿を取得（ユーザー情報も結合）
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            u.username as author_name
        FROM posts p
        LEFT JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$perPage, $offset]);
    $posts = $stmt->fetchAll();

} catch (Exception $e) {
    $_SESSION['flash_message'] = 'データの取得に失敗しました。';
    $_SESSION['flash_type'] = 'danger';
    $posts = [];
    $totalPages = 0;
}
?>

<main class="container">
    <div class="posts-header">
        <h1 class="page-title">📝 投稿一覧</h1>
        <?php if (isLoggedIn()): ?>
            <a href="new.php" class="btn btn-primary">＋ 新しい投稿を書く</a>
        <?php endif; ?>
    </div>

    <?php if (empty($posts)): ?>
        <p class="no-posts">投稿がありません。</p>
    <?php else: ?>
        <div class="posts-list">
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <header class="post-header">
                        <h2 class="post-title">
                            <?php echo htmlspecialchars($post['title'] ?: '(タイトルなし)'); ?>
                        </h2>
                        <div class="post-meta">
                            <span class="post-author">
                                投稿者: <?php echo htmlspecialchars($post['author_name'] ?? '不明'); ?>
                            </span>
                            <span class="post-date">
                                <?php echo (new DateTime($post['created_at']))->format('Y年n月j日 H:i'); ?>
                            </span>
                            <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                <span class="post-updated">
                                    (更新: <?php echo (new DateTime($post['updated_at']))->format('Y年n月j日 H:i'); ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                    </header>

                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post['body'])); ?>
                    </div>

                    <?php if (isLoggedIn() && $post['user_id'] === getCurrentUserId()): ?>
                        <footer class="post-actions">
                            <a href="edit.php?id=<?php echo $post['id']; ?>" 
                               class="btn btn-secondary btn-sm">
                                編集
                            </a>
                            <a href="delete.php?id=<?php echo $post['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('この投稿を削除してもよろしいですか？');">
                                削除
                            </a>
                        </footer>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>