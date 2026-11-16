# 弁護士検索システム

[![CI](https://github.com/rintarouchida/ben54-labo/actions/workflows/ci.yml/badge.svg)](https://github.com/rintarouchida/ben54-labo/actions/workflows/ci.yml)

都道府県・取扱分野・キーワードで弁護士を検索できるSPA。要件は [ben54-labo.md](ben54-labo.md)、作業の分割は [docs/](docs/README.md) を参照。

> **掲載データはすべて架空です。** 弁護士・法律事務所の氏名、生年月日、住所、電話番号などは Seeder が生成したダミーデータで、実在の人物・団体とは関係ありません。

| 区分 | 技術 |
| --- | --- |
| バックエンド | Laravel 12 / PHP 8.4(`backend/`) |
| フロントエンド | React 19 + TypeScript(strict)+ Vite、React Router 7、TanStack Query 5 + axios、Tailwind CSS 4(`frontend/`) |
| DB | MySQL 8.4 |
| 実行環境 | Docker Compose(`db` / `api` / `front` の3コンテナ) |

## 起動手順

必要なもの:Docker / Docker Compose v2。ホストに PHP・Composer・Node.js は不要。

```bash
git clone https://github.com/rintarouchida/ben54-labo.git
cd ben54-labo
cp backend/.env.example backend/.env
docker compose up -d --wait                          # 3コンテナが使える状態になるまで待つ
docker compose exec api php artisan key:generate
docker compose exec api php artisan migrate:fresh --seed   # 都道府県47件・取扱分野12件・弁護士1,000件を投入
```

ブラウザで http://localhost:5173 を開く。

- 初回の `docker compose up` では、`api` コンテナが `composer install`、`front` コンテナが `npm install` を実行してからサーバーを起動する。`--wait` を付けると、両方の準備ができるまでコマンドが戻らない(初回は1分前後。2回目以降は数秒)
- 使うポート:5173(画面)、8000(API)、3306(MySQL)。ほかのアプリが使っていると起動に失敗する
- ブラウザは `front` にだけ接続し、`/api` へのリクエストは Vite のプロキシで `api` コンテナに転送する。API を直接呼ぶときは http://localhost:8000/api/v1

### 停止・データの作り直し

```bash
docker compose stop                                  # 停止(データは残る)
docker compose exec api php artisan migrate:fresh --seed   # ダミーデータを作り直す
docker compose down -v                               # コンテナとボリューム(DB・node_modules)をすべて削除
```

## 画面構成

| ID | 画面 | URL | 内容 |
| --- | --- | --- | --- |
| SCR-01 | 弁護士検索・一覧 | `/` | 上部に検索フォーム(都道府県・取扱分野・キーワード)、下部に総件数・並び順・結果カード20件・ページャー |
| SCR-02 | 弁護士詳細 | `/lawyers/:id` | 弁護士の全項目と「検索結果に戻る」リンク |
| SCR-03 | ページが見つかりません | 上記以外、存在しない弁護士ID | メッセージとトップへのリンク |

- 検索条件・並び順・ページ番号は URL のクエリ文字列に持つ(例:`/?prefecture_id=13&practice_area_ids[]=1&keyword=山田&sort=age&page=2`)。再読み込み・ブラウザの戻る・URL の共有で同じ結果を表示できる
- 詳細画面の「検索結果に戻る」は、遷移元の検索条件とページに戻る
- 入力エラー(422)は項目の近くに、サーバーエラー(500)・通信エラーは画面上部に「再読み込み」ボタン付きで表示する
- スマートフォン(375px〜)と PC(1280px〜)に対応する

## API一覧

ベース URL は `http://localhost:8000/api/v1`。すべて認証不要の GET で、JSON(UTF-8)を返す。

| ID | パス | 内容 | 正常 | 異常 |
| --- | --- | --- | --- | --- |
| API-01 | `/lawyers` | 弁護士を検索し、20件ずつ返す | 200 | 422, 500 |
| API-02 | `/lawyers/{id}` | 弁護士1件の詳細を返す | 200 | 404, 500 |
| API-03 | `/prefectures` | 都道府県47件を返す | 200 | 500 |
| API-04 | `/practice-areas` | 取扱分野の一覧を返す | 200 | 500 |

API-01 のパラメータ(すべて任意):

| パラメータ | 内容 |
| --- | --- |
| `prefecture_id` | 都道府県 ID(1件) |
| `practice_area_ids[]` | 取扱分野 ID(複数指定は OR) |
| `keyword` | 名前・フリガナ・事務所名・自己紹介の部分一致。空白区切りの複数語は AND。100文字まで |
| `sort` | `case_count`(実績数が多い順、初期値)/ `age`(年齢が高い順)/ `name`(名前順) |
| `page` | ページ番号(1以上) |

```bash
curl 'http://localhost:8000/api/v1/lawyers?prefecture_id=13&practice_area_ids[]=1&keyword=山田&sort=age'
```

- エラーのボディは `{"message": "..."}`(422 は加えて `errors` に項目ごとの日本語メッセージ)。スタックトレースは返さない
- レート制限は1IPあたり毎分60回(超えると429)
- レスポンスの詳しい形式は要件定義書の5章を参照

## テスト

```bash
docker compose exec api php artisan test                         # 全テスト
docker compose exec api php artisan test --filter=test_search_  # テスト名で絞り込む
docker compose exec api php artisan test --coverage --min=80     # app/ の行カバレッジ(80%未満なら失敗)
```

- テストは db コンテナ内のテスト用 DB `lawyer_search_test` を使う(開発用のデータは消えない)。`RefreshDatabase` と Factory でテストごとにデータを作るため、Seeder の1,000件には依存しない
- `tests/Feature/Api/`:API ごとの Feature テスト(検索条件・ソート・ページング・バリデーション・404/500・レート制限など)
- `tests/Unit/`:Service・モデルのスコープと年齢の計算・Enum・LIKE のエスケープ・Seeder の Unit テスト。年齢は `travelTo()` で日時を固定して確かめる
- フロントエンドは ESLint・Prettier・TypeScript の型チェック(ビルド)で確かめる。コマンドは下の「push 前に手元で同じチェックを行う」を参照

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
