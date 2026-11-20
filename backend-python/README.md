# 弁護士検索API(FastAPI版)

Laravel 版(`backend/`)と同じ API を FastAPI で作り直したもの。仕様は [要件定義書(FastAPI版)](../ben54-labo-python.md)、作業は [docs/python/](../docs/python/README.md) のチケットで進める。

## 起動

```bash
docker compose up -d --wait                                # api-py(:8001)も一緒に起動する
docker compose exec api-py uv run alembic upgrade head     # テーブルを作る
curl http://localhost:8001/up                              # {"status":"ok"}
```

`.env` は無くても動く(省略時は compose の値になる。`app/core/config.py`)。`/docs`(OpenAPI の画面)を使うときは `.env.example` を `.env` にコピーし、`APP_DEBUG=true` のまま `docker compose restart api-py` する。

### db のボリュームを作ったのが FastAPI 版より前のとき

`docker/mysql/init/` は db のボリュームを初めて作ったときにしか実行されない。`lawyer_search_py` が無いと api-py が起動しないので、一度だけ手で実行する。

```bash
docker compose exec -T db mysql -uroot -proot < docker/mysql/init/02-create-python-db.sql
```

## コマンド

```bash
docker compose exec api-py uv run pytest                   # 全テスト(MySQL の lawyer_search_py_test を使う)
docker compose exec api-py uv run pytest --cov             # カバレッジ(合格基準: app/ の行カバレッジ80%以上)
docker compose exec api-py uv run pytest tests/feature -k 404   # フォルダやテスト名で絞る
docker compose exec api-py uv run ruff format .            # 整形
docker compose exec api-py uv run ruff check .             # lint
docker compose exec api-py uv run mypy app tests           # 型チェック(strict)
docker compose exec api-py uv run alembic revision --autogenerate -m "..."   # モデルの変更からマイグレーションを作る
```

依存を足すときは `docker compose exec api-py uv add パッケージ名`(テスト用は `--dev`)。`uv.lock` も一緒にコミットする。

## フロントを FastAPI 版につなぐ

```bash
API_PROXY_TARGET=http://api-py:8001 docker compose up -d front   # 戻すときは API_PROXY_TARGET を付けずに同じコマンド
```

## 構成

```
app/
  main.py          create_app(): CORS・例外ハンドラ・ルーターの登録
  core/            設定(config)・DB接続(database)・例外の変換(errors)・メッセージ(messages)
  models/          SQLAlchemy のモデル(Laravel の Model)
  schemas/         Pydantic の入出力の形(Laravel の FormRequest と API Resource)
  services/        処理の本体(Laravel の Service)
  routers/         エンドポイント。入出力だけを書く(Laravel の Controller)
  enums/ support/  ソート種別、LIKE のエスケープなど
  data/            都道府県・取扱分野・氏名などの固定データ
migrations/        Alembic のマイグレーション
tests/
  conftest.py      テスト用DBの準備、テストごとのロールバック、TestClient
  factories.py     テストデータの Factory
  unit/ feature/   関数単位のテスト / API 単位のテスト
```
