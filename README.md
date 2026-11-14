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
