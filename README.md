# 弁護士検索システム

[![CI](https://github.com/rintarouchida/ben54-labo/actions/workflows/ci.yml/badge.svg)](https://github.com/rintarouchida/ben54-labo/actions/workflows/ci.yml)

都道府県・取扱分野・キーワードで弁護士を検索できるSPA。要件は [ben54-labo.md](ben54-labo.md) を参照。

| 区分 | 技術 |
| --- | --- |
| バックエンド | Laravel 12 / PHP 8.4(`backend/`) |
| フロントエンド | React 19 + TypeScript + Vite(`frontend/`) |
| DB | MySQL 8.4 |

## 起動手順

必要なもの:Docker / Docker Compose v2

```bash
cp backend/.env.example backend/.env
docker compose up -d
docker compose exec api php artisan key:generate
docker compose exec api php artisan migrate:fresh --seed
```

ブラウザで http://localhost:5173 を開く。API は http://localhost:8000/api/v1 。

## テスト

```bash
docker compose exec api php artisan test --coverage
```

テストは dbコンテナ内の `lawyer_search_test` を使う。

## CI(GitHub Actions)

`.github/workflows/ci.yml` で、すべてのブランチへの push ごとに次のチェックを自動で実行する。Actions タブの「Run workflow」から手動でも実行できる。結果はプルリクの画面と、上部の CI バッジに表示される。

| ジョブ | 実行内容 | 所要時間の目安 |
| --- | --- | --- |
| Backend | MySQL 8.4 を起動 → `composer install` → Pint の整形チェック → 全テスト + `app/` の行カバレッジ80%以上 | 約1分 |
| Frontend | Node 22 で `npm ci` → ESLint → Prettier の整形チェック → 型チェックとビルド | 約20〜40秒 |

2つのジョブは並列に動き、どちらかが失敗すると CI 全体が失敗になる。

### 仕組み

- **DB**:`docker-compose.yml` の `db` サービスを `docker compose up -d --wait db` で起動する。照合順序(`utf8mb4_0900_ai_ci`)、タイムゾーン(+09:00)、テスト用DB `lawyer_search_test` の作成が、ローカルとまったく同じになる
- **PHP**:`shivammathur/setup-php` で PHP 8.4 と拡張(`pdo_mysql` など)、カバレッジ計測用の pcov を入れる。`.env` は `.env.example` から作り、`DB_HOST=127.0.0.1` でホストに公開した db コンテナに接続する
- **キャッシュ**:Composer と npm の依存はキャッシュし、2回目以降の実行を速くする
- **権限**:ワークフローに渡す `GITHUB_TOKEN` は `contents: read`(読み取りのみ)に絞っている
- **重複実行の取り消し**:同じブランチに続けて push した場合、古い実行は取り消して最新のコミットだけを検査する
- **費用**:公開リポジトリのため、GitHub ホストの標準ランナーは無料で、実行時間の上限もない

### push 前に手元で同じチェックを行う

CI が失敗しないよう、push 前に次のコマンドを通しておく。

```bash
# バックエンド
docker compose exec api ./vendor/bin/pint --test
docker compose exec api php artisan test --coverage --min=80

# フロントエンド
docker compose exec front npm run lint
docker compose exec front npm run format:check
docker compose exec front npm run build
```

整形チェックで失敗したときは、`docker compose exec api ./vendor/bin/pint`(PHP)、`docker compose exec front npm run format`(フロント)で自動整形できる。

### CI が失敗したとき

1. プルリクの「Checks」タブ、またはリポジトリの「Actions」タブで、失敗したジョブとステップを開く
2. Backend のテストが失敗した場合は、最後の「失敗時に Laravel のログを表示」ステップに `storage/logs` の内容が出る
3. 上の「push 前に手元で同じチェックを行う」のコマンドで再現し、直してから push し直す
