<?php
require_once __DIR__ . '/../app/includes/database.php';

// テスト用データベース接続情報を上書き
class TestDatabase extends Database {
    protected function __construct() {
        $host = 'db';
        $db   = 'blog_testing';
        $user = 'user';
        $pass = 'password';

        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user, 
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("データベース接続エラー: " . $e->getMessage());
        }
    }
    
    public static function resetInstance() {
        self::$instance = null;
    }
}

// テスト用データベースにテーブルを作成
$pdo = TestDatabase::getInstance()->getConnection();

// 外部キーチェックを一時的に無効にする
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// テーブルを削除（順序を考慮）
$pdo->exec("DROP TABLE IF EXISTS posts");
$pdo->exec("DROP TABLE IF EXISTS users");

// 外部キーチェックを再度有効にする
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// usersテーブル
$pdo->exec("
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

// postsテーブル
$pdo->exec("
    CREATE TABLE posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        body TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )
");

echo "Test database schema created successfully.\n"; 