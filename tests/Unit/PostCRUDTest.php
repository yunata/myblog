<?php
namespace Tests\Unit;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

/**
 * ブログ投稿のCRUD操作をテストするクラス
 */
class PostCRUDTest extends TestCase
{
    private ?PDO $pdo = null;
    private int $testUserId;
    
    /**
     * テスト前の初期化処理
     */
    protected function setUp(): void
    {
        // テスト用データベース接続
        $host = getenv('DB_HOST') ?: 'db';
        $dbname = getenv('DB_NAME') ?: 'blog_testing';  // blog_testingに変更
        $user = getenv('DB_USER') ?: 'user';
        $pass = getenv('DB_PASSWORD') ?: 'password';
        
        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            $this->fail("データベース接続エラー: " . $e->getMessage());
        }
        
        // テスト用ユーザーを作成または取得
        $this->createTestUser();
    }
    
    /**
     * テスト後のクリーンアップ
     */
    protected function tearDown(): void
    {
        // テスト投稿を削除
        $this->pdo->exec("DELETE FROM posts WHERE user_id = {$this->testUserId}");
        
        // PDO接続をクローズ
        $this->pdo = null;
    }
    
    /**
     * テスト用ユーザーを作成または取得する
     */
    private function createTestUser(): void
    {
        // ユーザーが存在するか確認
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = 'testuser'");
        $stmt->execute();
        $user = $stmt->fetch();
        
        if ($user) {
            $this->testUserId = $user['id'];
        } else {
            // テスト用ユーザーを作成
            $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password)
                VALUES ('testuser', 'test@example.com', :password)
            ");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
            
            $this->testUserId = $this->pdo->lastInsertId();
        }
    }
    
    /**
     * 投稿作成のテスト
     */
    public function testCreatePost(): int
    {
        $title = 'テスト投稿タイトル_' . uniqid();
        $body = 'これはテスト投稿の本文です。' . date('Y-m-d H:i:s');
        
        // 投稿を作成
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (user_id, title, body)
            VALUES (:user_id, :title, :body)
        ");
        $stmt->bindParam(':user_id', $this->testUserId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':body', $body);
        $result = $stmt->execute();
        
        $this->assertTrue($result, '投稿の作成に失敗しました');
        
        $postId = $this->pdo->lastInsertId();
        $this->assertGreaterThan(0, $postId, '投稿IDが正しく取得できませんでした');
        
        // 作成した投稿を確認
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->bindParam(':id', $postId);
        $stmt->execute();
        $post = $stmt->fetch();
        
        $this->assertNotFalse($post, '作成した投稿が見つかりませんでした');
        $this->assertEquals($title, $post['title'], '投稿タイトルが一致しません');
        $this->assertEquals($body, $post['body'], '投稿本文が一致しません');
        $this->assertEquals($this->testUserId, $post['user_id'], 'ユーザーIDが一致しません');
        
        return $postId;
    }
    
    /**
     * 投稿読み取りのテスト
     * @depends testCreatePost
     */
    public function testReadPost(int $postId = null): int
    {
        // PDO接続が切れていた場合の再接続
        if ($this->pdo === null) {
            $host = getenv('DB_HOST') ?: 'db';
            $dbname = getenv('DB_NAME') ?: 'blog_testing';
            $user = getenv('DB_USER') ?: 'user';
            $pass = getenv('DB_PASSWORD') ?: 'password';
            
            try {
                $this->pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                $this->fail("データベース接続エラー: " . $e->getMessage());
                return 0; // ダミー値を返す
            }
        }
        
        // テスト用ユーザーがセットされていない場合の対処
        if (empty($this->testUserId)) {
            $this->createTestUser();
        }
        
        // 引数のpostIdがnullの場合、新しい投稿を作成する
        if ($postId === null) {
            $title = 'テスト投稿タイトル_' . uniqid();
            $body = 'これはテスト投稿の本文です。' . date('Y-m-d H:i:s');
            
            $stmt = $this->pdo->prepare("
                INSERT INTO posts (user_id, title, body)
                VALUES (:user_id, :title, :body)
            ");
            $stmt->bindParam(':user_id', $this->testUserId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':body', $body);
            $stmt->execute();
            
            $postId = $this->pdo->lastInsertId();
        }
        
        // 読み取る前に投稿が存在するか確認
        $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE id = :id");
        $checkStmt->bindParam(':id', $postId);
        $checkStmt->execute();
        $count = (int)$checkStmt->fetchColumn();
        
        if ($count === 0) {
            // 投稿が見つからない場合、新しい投稿を作成
            $title = 'リカバリー投稿_' . uniqid();
            $body = 'これはリカバリー投稿です。' . date('Y-m-d H:i:s');
            
            $stmt = $this->pdo->prepare("
                INSERT INTO posts (id, user_id, title, body)
                VALUES (:id, :user_id, :title, :body)
            ");
            $stmt->bindParam(':id', $postId);
            $stmt->bindParam(':user_id', $this->testUserId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':body', $body);
            
            try {
                $stmt->execute();
            } catch (PDOException $e) {
                // IDを指定せずに新しい投稿を作成
                $stmt = $this->pdo->prepare("
                    INSERT INTO posts (user_id, title, body)
                    VALUES (:user_id, :title, :body)
                ");
                $stmt->bindParam(':user_id', $this->testUserId);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':body', $body);
                $stmt->execute();
                
                $postId = $this->pdo->lastInsertId();
            }
        }
        
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->bindParam(':id', $postId);
        $stmt->execute();
        $post = $stmt->fetch();
        
        if ($post === false) {
            $this->fail("投稿ID {$postId} が読み取れませんでした");
        }
        
        $this->assertNotFalse($post, '投稿が読み取れませんでした');
        $this->assertEquals($postId, $post['id'], '投稿IDが一致しません');
        $this->assertEquals($this->testUserId, $post['user_id'], 'ユーザーIDが一致しません');
        $this->assertNotEmpty($post['title'], 'タイトルが空です');
        $this->assertNotEmpty($post['body'], '本文が空です');
        
        return $postId;
    }
    
    /**
     * 投稿更新のテスト
     * @depends testReadPost
     */
    public function testUpdatePost(int $postId = null): int
    {
        // PDO接続が切れていた場合の再接続
        if ($this->pdo === null) {
            $host = getenv('DB_HOST') ?: 'db';
            $dbname = getenv('DB_NAME') ?: 'blog_testing';
            $user = getenv('DB_USER') ?: 'user';
            $pass = getenv('DB_PASSWORD') ?: 'password';
            
            try {
                $this->pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                $this->fail("データベース接続エラー: " . $e->getMessage());
                return 0; // ダミー値を返す
            }
        }
        
        // テスト用ユーザーがセットされていない場合の対処
        if (empty($this->testUserId)) {
            $this->createTestUser();
        }
        
        // 引数がnullの場合は、新しい投稿を作成
        if ($postId === null) {
            $title = 'テスト投稿タイトル_' . uniqid();
            $body = 'これはテスト投稿の本文です。' . date('Y-m-d H:i:s');
            
            $stmt = $this->pdo->prepare("
                INSERT INTO posts (user_id, title, body)
                VALUES (:user_id, :title, :body)
            ");
            $stmt->bindParam(':user_id', $this->testUserId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':body', $body);
            $stmt->execute();
            
            $postId = $this->pdo->lastInsertId();
        }
        
        // 読み取る前に投稿が存在するか確認
        $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE id = :id");
        $checkStmt->bindParam(':id', $postId);
        $checkStmt->execute();
        $count = (int)$checkStmt->fetchColumn();
        
        if ($count === 0) {
            // 投稿が見つからない場合、新しい投稿を作成
            $title = 'リカバリー投稿_' . uniqid();
            $body = 'これはリカバリー投稿です。' . date('Y-m-d H:i:s');
            
            $stmt = $this->pdo->prepare("
                INSERT INTO posts (user_id, title, body)
                VALUES (:user_id, :title, :body)
            ");
            $stmt->bindParam(':user_id', $this->testUserId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':body', $body);
            $stmt->execute();
            
            $postId = $this->pdo->lastInsertId();
        }
        
        $newTitle = '更新されたタイトル_' . uniqid();
        $newBody = '更新された本文です。' . date('Y-m-d H:i:s');
        
        $stmt = $this->pdo->prepare("
            UPDATE posts 
            SET title = :title, body = :body 
            WHERE id = :id
        ");
        $stmt->bindParam(':title', $newTitle);
        $stmt->bindParam(':body', $newBody);
        $stmt->bindParam(':id', $postId);
        $result = $stmt->execute();
        
        $this->assertTrue($result, '投稿の更新に失敗しました');
        
        // 更新された投稿を確認
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->bindParam(':id', $postId);
        $stmt->execute();
        $post = $stmt->fetch();
        
        if ($post === false) {
            $this->fail("更新された投稿が見つかりませんでした");
        }
        
        $this->assertNotFalse($post, '更新された投稿が見つかりませんでした');
        $this->assertEquals($newTitle, $post['title'], '更新されたタイトルが一致しません');
        $this->assertEquals($newBody, $post['body'], '更新された本文が一致しません');
        
        return $postId;
    }
    
    /**
     * 投稿削除のテスト
     * @depends testUpdatePost
     */
    public function testDeletePost(int $postId = null): void
    {
        // PDO接続が切れていた場合の再接続
        if ($this->pdo === null) {
            $host = getenv('DB_HOST') ?: 'db';
            $dbname = getenv('DB_NAME') ?: 'blog_testing';
            $user = getenv('DB_USER') ?: 'user';
            $pass = getenv('DB_PASSWORD') ?: 'password';
            
            try {
                $this->pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                $this->fail("データベース接続エラー: " . $e->getMessage());
                return;
            }
        }
        
        // テスト用ユーザーがセットされていない場合の対処
        if (empty($this->testUserId)) {
            $this->createTestUser();
        }
        
        // 引数がnullの場合は、新しい投稿を作成
        if ($postId === null) {
            $title = 'テスト投稿タイトル_' . uniqid();
            $body = 'これはテスト投稿の本文です。' . date('Y-m-d H:i:s');
            
            $stmt = $this->pdo->prepare("
                INSERT INTO posts (user_id, title, body)
                VALUES (:user_id, :title, :body)
            ");
            $stmt->bindParam(':user_id', $this->testUserId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':body', $body);
            $stmt->execute();
            
            $postId = $this->pdo->lastInsertId();
        }
        
        // 読み取る前に投稿が存在するか確認
        $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE id = :id");
        $checkStmt->bindParam(':id', $postId);
        $checkStmt->execute();
        $count = (int)$checkStmt->fetchColumn();
        
        if ($count === 0) {
            // 投稿が見つからない場合、新しい投稿を作成
            $title = 'リカバリー投稿_' . uniqid();
            $body = 'これはリカバリー投稿です。' . date('Y-m-d H:i:s');
            
            $stmt = $this->pdo->prepare("
                INSERT INTO posts (user_id, title, body)
                VALUES (:user_id, :title, :body)
            ");
            $stmt->bindParam(':user_id', $this->testUserId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':body', $body);
            $stmt->execute();
            
            $postId = $this->pdo->lastInsertId();
        }
        
        // 投稿を削除
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->bindParam(':id', $postId);
        $result = $stmt->execute();
        
        $this->assertTrue($result, '投稿の削除に失敗しました');
        
        // 削除されたことを確認
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE id = :id");
        $stmt->bindParam(':id', $postId);
        $stmt->execute();
        $count = (int)$stmt->fetchColumn();
        
        $this->assertEquals(0, $count, '投稿が正しく削除されていません');
    }
    
    /**
     * 投稿一覧取得のテスト
     */
    public function testListPosts(): void
    {
        // テスト用ユーザーがセットされていない場合の対処
        if (empty($this->testUserId)) {
            $this->createTestUser();
        }
        
        // PDO接続が切れていた場合の再接続
        if ($this->pdo === null) {
            $host = getenv('DB_HOST') ?: 'db';
            $dbname = getenv('DB_NAME') ?: 'blog_testing';
            $user = getenv('DB_USER') ?: 'user';
            $pass = getenv('DB_PASSWORD') ?: 'password';
            
            try {
                $this->pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                $this->fail("データベース接続エラー: " . $e->getMessage());
                return;
            }
        }
        
        // テスト投稿を複数作成
        $postIds = [];
        for ($i = 0; $i < 3; $i++) {
            $title = "テスト投稿_{$i}_" . uniqid();
            $body = "テスト本文_{$i}_" . date('Y-m-d H:i:s');
            
            $stmt = $this->pdo->prepare("
                INSERT INTO posts (user_id, title, body)
                VALUES (:user_id, :title, :body)
            ");
            $stmt->bindParam(':user_id', $this->testUserId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':body', $body);
            $stmt->execute();
            
            $postIds[] = $this->pdo->lastInsertId();
        }
        
        // 投稿一覧を取得
        $stmt = $this->pdo->prepare("
            SELECT * FROM posts 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC
        ");
        $stmt->bindParam(':user_id', $this->testUserId);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        
        // 少なくとも作成した投稿数があることを確認
        $this->assertGreaterThanOrEqual(count($postIds), count($posts), '取得した投稿数が正しくありません');
        
        // 作成した投稿IDがすべて含まれているか確認
        $fetchedIds = array_column($posts, 'id');
        foreach ($postIds as $id) {
            $this->assertContains((string)$id, array_map('strval', $fetchedIds), "投稿ID {$id} が一覧に含まれていません");
        }
        
        // 後処理: テスト投稿を削除
        $idList = implode(',', $postIds);
        $this->pdo->exec("DELETE FROM posts WHERE id IN ({$idList})");
    }
} 