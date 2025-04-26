# マイブログ

シンプルなブログシステム

## プロジェクト構成

```
myblog/
├── src/          # PHPソースファイル
│   ├── index.php     # 投稿一覧ページ
│   ├── create.php    # 投稿作成処理
│   ├── update.php    # 投稿更新処理
│   ├── edit.php      # 投稿編集ページ
│   ├── delete.php    # 投稿削除処理
│   ├── new.php       # 新規投稿ページ
│   ├── login.php     # ログインページ
│   ├── login_check.php # ログイン処理
│   ├── logout.php    # ログアウト処理
│   └── hash.php      # パスワードハッシュ化
├── includes/     # 共通のPHPファイル
│   ├── database.php  # データベース接続
│   ├── session.php   # セッション管理
│   ├── auth.php      # 認証関連
│   ├── header.php    # 共通ヘッダー
│   ├── footer.php    # 共通フッター
│   └── csrf_token.php # CSRF対策
└── public/       # 公開アセット
    ├── css/         # スタイルシート
    ├── js/          # JavaScript
    └── images/      # 画像ファイル
```

## セットアップ

1. データベースの設定
   - MySQLデータベースを作成
   - `includes/database.php`の接続情報を更新

2. Webサーバーの設定
   - ドキュメントルートを`public`ディレクトリに設定
   - PHPのバージョン: 7.4以上を推奨

3. 権限の設定
   - 適切なファイルパーミッションを設定
   - セッションディレクトリの書き込み権限を確認

## 機能

- ユーザー認証（ログイン/ログアウト）
- ブログ投稿の作成/編集/削除
- ページネーション
- レスポンシブデザイン
- XSS対策
- CSRF対策
- セキュアなセッション管理 