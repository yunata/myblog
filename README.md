# マイブログ - PHPブログアプリケーション

シンプルなPHP製ブログアプリケーションです。

## 機能

- ユーザー登録とログイン認証
- 投稿の作成、編集、削除
- ページネーション
- CSRF対策
- レスポンシブデザイン

## 技術スタック

- PHP 8.3
- MySQL 8.0
- Docker
- Composer (依存関係管理)
- PHPUnit (テスト)

## ディレクトリ構造

```
myblog/
├── app/                 # アプリケーションコード
│   ├── controllers/     # コントローラー
│   └── includes/        # 共通関数・クラス
│       ├── auth.php     # 認証関連
│       ├── database.php # DB接続
│       ├── csrf_token.php # CSRF対策
│       ├── session.php  # セッション管理
│       ├── header.php   # 共通ヘッダー
│       └── footer.php   # 共通フッター
├── bootstrap.php        # アプリケーション初期化
├── public/              # ウェブルート
│   ├── index.php        # フロントコントローラー
│   ├── .htaccess        # Apache設定
│   └── css/             # CSSファイル
├── tests/               # テストコード
│   └── Unit/            # ユニットテスト
├── vendor/              # Composer依存関係（gitignore）
├── .env.example         # 環境設定例
├── composer.json        # Composer設定
├── docker-compose.yml   # Docker設定
├── Dockerfile           # Dockerビルド設定
└── phpunit.xml          # PHPUnit設定
```

## インストール

### 事前準備

- Docker と Docker Compose がインストールされていること

### セットアップ手順

1. リポジトリをクローン
   ```
   git clone https://github.com/yourusername/myblog.git
   cd myblog
   ```

2. 環境設定ファイルをコピー
   ```
   cp .env.example .env
   ```

3. Dockerコンテナをビルド・起動
   ```
   docker-compose up -d
   ```

4. 依存関係をインストール
   ```
   docker exec -it myblog-web composer install
   ```

5. データベースの初期化
   ```
   docker exec -it myblog-db mysql -uuser -ppassword blog < init.sql
   ```

6. ブラウザでアクセス
   ```
   http://localhost:8080
   ```

## テスト実行

```
docker exec -it myblog-web ./vendor/bin/phpunit
```

## デプロイ方法

### 本番環境用の設定

1. `.env`ファイルを本番環境用に設定
   ```
   APP_ENV=production
   APP_DEBUG=false
   ```

2. Composerの依存関係を最適化
   ```
   composer install --no-dev --optimize-autoloader
   ```

3. `.htaccess`の本番環境用設定を有効化
   - HTTPSリダイレクト
   - エラー非表示

## ライセンス

MIT 