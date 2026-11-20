# P01 基盤構築

- 参照: 要件定義書(FastAPI版)0章、1章、6章、7章、8.1節
- 担当: Claude

## 概要
FastAPI 版の土台を作る。この時点では API-01〜04 はまだ無く、`/up` と例外の変換だけが動く。

## 完了条件
- `docker compose up -d --wait` で api-py(:8001)が healthy になる
- `uv run alembic upgrade head` で5つのテーブルができる
- `uv run pytest --cov` が成功する

## Todo

### コンテナ
- [x] `docker/python/Dockerfile`(python:3.13-slim + uv 0.8.4。仮想環境は `/opt/venv`)
- [x] `docker-compose.yml` に `api-py` を追加(:8001、`uv sync --frozen` のあと `uvicorn --reload`、`/up` でヘルスチェック)
- [x] `front` の `API_PROXY_TARGET` を環境変数で切り替えられるようにする(初期値は Laravel 版)
- [x] `docker/mysql/init/02-create-python-db.sql`(`lawyer_search_py` / `lawyer_search_py_test` とユーザー `fastapi`)

### プロジェクト
- [x] `pyproject.toml`(依存、Ruff、mypy strict、pytest、coverage の設定)と `uv.lock`
- [x] `.env.example`、`.gitignore`
- [x] `app/data/`(都道府県・取扱分野・氏名・人口の固定データ。Laravel 版の `database/data/` から変換)

### アプリ
- [x] `app/core/config.py`: `Settings`(環境変数。省略時は compose の値)と `database_url()`
- [x] `app/core/database.py`: `create_db_engine()`、`SessionLocal`、依存関数 `get_session()`
- [x] `app/core/messages.py`: 404 / 500 のメッセージ
- [x] `app/core/errors.py`: `NotFoundError`、404 / 405 / 500 を `{ message }` に変換するハンドラ
- [x] `app/main.py`: `create_app()`(CORS、例外ハンドラ、`/api/v1` のルーター、`APP_DEBUG` のときだけ `/docs`)
- [x] `app/routers/health.py`: `GET /up`
- [x] `app/schemas/common.py`: `DataResponse[T]`(`{ "data": ... }`)

### モデル・マイグレーション
- [x] `app/models/base.py`: `Base`(制約名の規則を Laravel と揃える)、`TimestampMixin`
- [x] `Prefecture` / `PracticeArea` / `LawOffice` / `Lawyer` / `lawyer_practice_area`(6章の型・インデックス・外部キー。リレーションは `lazy="raise"`)
- [x] Alembic の設定(`alembic.ini`、`migrations/env.py`)と最初のマイグレーション `0001_create_tables.py`

### テスト基盤
- [x] `tests/conftest.py`: テスト開始時に Alembic でテーブルを作り直す、テストごとにロールバック、`client` / `app` / `db_session`
- [x] `client` は API を呼ぶたびにテスト用セッションを空にする(Factory で作ったオブジェクトを使い回さず、本番と同じくDBから読み直させる)
- [x] `tests/factories.py`: 4つのモデルの Factory(Laravel 版の Factory と同じ生成ルール)
- [x] テスト: 設定(DBのURL、環境変数の優先)
- [x] テスト: `get_session` がセッションを閉じる
- [x] テスト: マイグレーションとモデルの定義に差がない、照合順序
- [x] テスト: リレーション、`lazy="raise"`、タイムスタンプ、CASCADE、複合主キー、外部キー
- [x] テスト: `/up`、未定義のパスの404、`NotFoundError` の404、405、500(スタックトレースを返さない)、CORS、`/docs` の公開条件
