# PHP 8.2 + SQLite TODO アプリ

外部ライブラリ不要のシンプルな TODO アプリです。TODO の追加・編集・完了状態の切り替え・削除、カテゴリの作成・編集・削除とTODOへの割り当て、カテゴリ別（未分類を含む）の絞り込み表示に対応しています。SQLite データベースは初回アクセス時に `data/todos.sqlite` として自動作成されます。

データベースへのアクセス処理は `lib/model.php`、リダイレクトやCSRF検証などの処理は `lib/controller.php`、TODO／カテゴリの作成・編集・削除の処理は `lib/actions.php`、共通HTMLは `lib/view.php` にまとめています。`index.php` はリクエストを受け付け、表示条件を決定して画面を描画します。

## 起動方法

PHP に SQLite 拡張が有効な環境で、プロジェクト直下から次を実行します。

```bash
php -S localhost:8000
```

ブラウザで http://localhost:8000 を開いてください。
