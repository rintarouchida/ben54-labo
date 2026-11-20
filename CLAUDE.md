# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

弁護士検索SPA(ポートフォリオ)。機能・API仕様・DB設計・必須テストケースは要件定義書 `ben54-labo.md` が唯一の正とする。実装前に該当する章を必ず読むこと。

作業は `docs/` 配下の連番チケットに分割している(一覧は `docs/README.md`)。各チケットには Todo のチェックリストがあり、Todo を終えたら同じ変更の中で `- [ ]` を `- [x]` にする。

バックエンドを FastAPI で作り直している(`backend-python/`)。こちらは要件定義書 `ben54-labo-python.md` が唯一の正で、チケットは `docs/python/`(一覧は `docs/python/README.md`)。移行が終わるまで Laravel 版(`backend/`、`ben54-labo.md`、`docs/01〜16`)はそのまま残す。P03 はユーザーが手動で実装するチケットなので、頼まれない限りコードを書かず、レビューと質問への回答にとどめる。

## 構成

- `backend/` — Laravel 12 の REST API(PHP 8.4)。ベースパスは `/api/v1`(`bootstrap/app.php` の `apiPrefix` で設定)。ルートは `routes/api.php` に書く
- `frontend/` — React 19 + TypeScript(strict)+ Vite、React Router 7、TanStack Query 5 + axios、Tailwind CSS 4
- `docker/` — PHP の Dockerfile、MySQL の設定と初期化SQL(テスト用DB `lawyer_search_test` を作成)
- `backend-python/` — FastAPI 版の REST API(Python 3.13、SQLAlchemy 2.1 同期 + PyMySQL、Alembic、Pydantic 2、uv)。手順と構成は `backend-python/README.md`
- `docker-compose.yml` — `db`(MySQL 8.4、:3306)、`api`(`php artisan serve`、:8000)、`api-py`(`uvicorn --reload`、:8001)、`front`(Vite 開発サーバー、:5173)の4サービス

ブラウザは `front` にだけ接続し、`/api` へのリクエストは Vite のプロキシで `api` コンテナに転送する(転送先は環境変数 `API_PROXY_TARGET`。未設定ならホスト上の `localhost:8000`)。CORS で許可するオリジンは `FRONTEND_URL`(`http://localhost:5173`)のみ。

DB の照合順序は `utf8mb4_0900_ai_ci`(Laravel の初期値 `utf8mb4_unicode_ci` から `config/database.php` で変更済み)。キャッシュは database ドライバで、レート制限が `cache` テーブルを使う。キューは使わない(`QUEUE_CONNECTION=sync`)。

## コマンド

```bash
docker compose up -d --wait                                     # 初回は composer install / npm install が終わるまで待つ
docker compose exec api php artisan migrate:fresh --seed
docker compose exec api php artisan test                         # 全テスト(SQLite ではなく MySQL の lawyer_search_test を使う)
docker compose exec api php artisan test --filter=test_search_  # 単一テスト / パターン指定
docker compose exec api php artisan test --coverage              # 合格基準: app/ の行カバレッジ80%以上
docker compose exec api ./vendor/bin/pint                        # PHP の整形
cd frontend && npm run lint && npm run format && npm run build
```

GitHub Actions(`.github/workflows/ci.yml`)が全ブランチへの push で、Pint の整形チェック・全テスト・カバレッジ80%以上と、フロントの ESLint・Prettier の整形チェック(`npm run format:check`)・ビルドを実行する。push 前に同じコマンドを手元で通しておくこと。

FastAPI 版のコマンドはすべて `docker compose exec api-py uv run ...`(`pytest --cov` / `ruff format .` / `ruff check .` / `mypy app tests`)。DB は `lawyer_search_py`(テストは `lawyer_search_py_test`、ユーザー `fastapi`)で、Laravel 版とは分けている。仮想環境は名前付きボリュームの `/opt/venv`。テストの `client` は API を呼ぶたびにテスト用セッションを `expunge_all()` する(本番と同じく、DBから読み直させるため)。

`artisan` / `composer` / テストは `api` コンテナ内で実行する。`.env` は `DB_HOST=db` で、この名前は compose のネットワーク内でしか解決できないため、ホストで `php artisan` を実行すると `getaddrinfo for db failed` になる(回避するなら先頭に `DB_HOST=127.0.0.1` を付ける)。

`front` コンテナの `node_modules` は名前付きボリュームに置いている(Linux 用バイナリ)。ホストの `frontend/node_modules` とは別物。`backend/composer.json` は `config.platform.php` を 8.4 に固定しており、ホストの composer(PHP 8.5)でもコンテナの PHP に合わせて依存を解決する。

## 規約(要件定義書より)

- バックエンドの責務分割: Controller(入出力のみ)→ FormRequest(`SearchLawyerRequest`)→ Service(`LawyerSearchService`)→ Model(スコープ `inPrefecture` / `withAnyPracticeArea` / `matchKeyword`、`age` アクセサ)→ API Resource
- ソート種別は Enum `LawyerSortType` で定義し、同値のときの並びとして必ず `id ASC` を付ける
- 取扱分野の OR 条件は JOIN ではなく `whereHas` + `whereIn`(EXISTS)で書く。`lawOffice.prefecture` と `practiceAreas` は事前読み込みする。LIKE に渡す前に `%` `_` `\` をエスケープする
- PHP はすべてのファイルに `declare(strict_types=1);` と引数・戻り値の型を書く
- エラーメッセージは日本語(`lang/ja/validation.php`)。404 / 500 のボディは `{ "message": ... }` で、スタックトレースは返さない。レート制限は1IPあたり毎分60回
- テスト名は `test_対象_条件_期待結果`。`RefreshDatabase` と Factory を使い(Seeder の1,000件に依存しない)、年齢のテストは `travelTo()` で日時を固定する。メソッドを追加したら同じコミットで正常系と異常系のテストを足す
- フロントは `pages` / `components` / `api` / `types` に分ける。検索条件は URL のクエリ文字列に持つ。`dangerouslySetInnerHTML` は使わない。都道府県と取扱分野は必ず API から取得する
