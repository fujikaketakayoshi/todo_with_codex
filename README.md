# PHP 8.3 + SQLite TODO アプリ

TODO の追加・編集・完了状態の切り替え・削除、タグの作成・編集・削除、複数タグの割り当て、タグ別（タグなしを含む）の絞り込み表示に対応したシンプルなアプリです。SQLite データベースは初回アクセス時に `data/todos.sqlite` として自動作成されます。既存のカテゴリデータは初回起動時にタグへ自動移行されます。

データベースへのアクセス処理は `lib/model.php`、リダイレクトやCSRF検証などの処理は `lib/controller.php`、TODO／カテゴリの作成・編集・削除の処理は `lib/actions.php`、共通HTMLは `lib/view.php` にまとめています。`index.php` はリクエストを受け付け、表示条件を決定して画面を描画します。

## CSSのビルド

画面はTailwind CSSで構成しています。初回のみNode.js依存をインストールし、CSSを生成してください。

```bash
npm install
npm run build:css
```

開発中にCSSを自動生成するには、次を実行します。

```bash
npm run watch:css
```

## 起動方法

PHP 8.3 と SQLite 拡張が有効な環境で、プロジェクト直下から次を実行します。

```bash
php -S localhost:8000
```

ブラウザで http://localhost:8000 を開いてください。
