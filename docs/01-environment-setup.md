# 01 開発環境構築

- 参照: 要件定義書 1章, 7.1, 7.2
- 時期: 1日目 午前

## 概要
`docker compose up -d` で db / api / front の3コンテナを一括起動できるようにする。

## 完了条件
- `docker compose up -d` で3コンテナが起動し、`http://localhost:5173` と `http://localhost:8000` にアクセスできる
- api コンテナから `lawyer_search` と `lawyer_search_test` に接続できる

## Todo
- [x] ディレクトリ構成を決める(`backend/` `frontend/` `docker/`)
- [x] Laravel 12 プロジェクトを作成(composer の platform を PHP 8.4 に固定)
- [x] React 19 + TypeScript + Vite プロジェクトを作成
- [x] React Router 7 / TanStack Query 5 / axios / Tailwind CSS 4 / Prettier を導入
- [x] `docker/php/Dockerfile`(pdo_mysql, intl, zip, bcmath, pcov)を作成
- [x] `docker/mysql`(utf8mb4_0900_ai_ci、テスト用DBの作成SQL)を作成
- [x] `docker-compose.yml`(db のヘルスチェック通過後に api を起動)を作成
- [x] Vite の `/api` プロキシを api コンテナに向ける
- [x] `.env.example` を MySQL / ja / ja_JP 用に設定
- [x] API のプレフィックスを `/api/v1` に設定
- [x] CORS の許可オリジンを `http://localhost:5173` のみにする
- [x] phpunit.xml のテスト用DBを MySQL `lawyer_search_test` にする
- [x] README に起動手順を書く
- [x] Docker をインストールし、`docker compose up -d` で3コンテナの起動を確認する
- [x] `docker compose exec api php artisan migrate` で DB 接続を確認する
- [x] `http://localhost:5173` で画面が表示されることを確認する
