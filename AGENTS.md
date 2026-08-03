# Project Manifest

## Goal
シンプルなToDoアプリを開発する。

## Technology
- PHP 8.3
- SQLite
- JavaScript（必要最低限）
- TailWind

## Coding Style
- PSR-12準拠
- 型宣言をできるだけ付ける
- コメントは必要最小限
- 関数は小さく保つ
- SQLインジェクション対策を行う
- セキュリティを優先する
- 可読性を重視する

## Database
- PDOを使用
- SQLインジェクション対策を行う
- SQLiteのみ使用

## Frontend
- Modern web best practicesを優先する
- 必要に応じてCSSやWeb標準APIを活用する
- CSSにはTailWindを用いること
- 依存ライブラリは必要性を検討して追加する

## Rules
- 外部ライブラリは必要最小限
- 動作するコードを優先
- セキュリティを考慮する# AGENTS.md

## Architecture
- ロジックはできるだけ関数に分ける
- HTMLとPHPを極端に混在させない
- DBアクセスは共通関数にまとめる

## Before Editing
- 既存コードを尊重する
- 必要以上のリファクタリングは行わない
- 動作に影響する変更は理由を説明する
