# 02 マイグレーション作成

- 参照: 要件定義書 6章(6.1〜6.5)
- 時期: 1日目 午前

## 概要
5テーブル(prefectures / practice_areas / law_offices / lawyers / lawyer_practice_area)を定義書どおりの型・制約・インデックスで作成する。外部キーの参照先が先に作られるよう、マイグレーションは prefectures → practice_areas → law_offices → lawyers → lawyer_practice_area の順にする。

## 完了条件
- `php artisan migrate:fresh` がエラーなく完了する
- 型・NULL可否・キー・インデックスが定義書と一致している(`SHOW CREATE TABLE` で確認)

## Todo

### 準備
- [x] 不要な初期マイグレーションを整理する(users / password_reset_tokens / sessions / jobs を削除し、User モデルと UserFactory も削除。cache はレート制限で使うため残す。キューは使わないため `QUEUE_CONNECTION=sync`)
- [x] 各マイグレーションファイルに `declare(strict_types=1);` を書く

### 6.1 prefectures(都道府県マスター)
- [x] `id`: TINYINT UNSIGNED、NOT NULL、PK。自動採番にせず JIS コード(1〜47)をそのまま入れる
- [x] `name`: VARCHAR(10)、NOT NULL、UNIQUE
- [x] `created_at` / `updated_at`: TIMESTAMP、NULL 可

### 6.2 practice_areas(取扱分野マスター)
- [x] `id`: BIGINT UNSIGNED、NOT NULL、PK(自動採番)
- [x] `name`: VARCHAR(50)、NOT NULL、UNIQUE
- [x] `created_at` / `updated_at`: TIMESTAMP、NULL 可

### 6.3 law_offices(法律事務所)
- [x] `id`: BIGINT UNSIGNED、NOT NULL、PK(自動採番)
- [x] `prefecture_id`: TINYINT UNSIGNED、NOT NULL、FK → prefectures.id、INDEX
- [x] `name`: VARCHAR(100)、NOT NULL(キーワード検索の対象)
- [x] `address`: VARCHAR(255)、NOT NULL(都道府県から書く)
- [x] `phone_number`: VARCHAR(20)、NOT NULL(ハイフン付き)
- [x] `created_at` / `updated_at`: TIMESTAMP、NULL 可

### 6.4 lawyers(弁護士)
- [x] `id`: BIGINT UNSIGNED、NOT NULL、PK(自動採番)
- [x] `law_office_id`: BIGINT UNSIGNED、NOT NULL、FK → law_offices.id、INDEX
- [x] `name`: VARCHAR(50)、NOT NULL(姓と名の間に半角スペース)
- [x] `name_kana`: VARCHAR(100)、NOT NULL、INDEX(全角カタカナ。名前順ソートに使う)
- [x] `birth_date`: DATE、NOT NULL、INDEX(年齢は保存しない。年齢順ソートに使う)
- [x] `registered_year`: SMALLINT UNSIGNED、NOT NULL
- [x] `case_count`: INT UNSIGNED、NOT NULL、初期値0、INDEX(実績数順ソートに使う)
- [x] `career`: TEXT、NOT NULL
- [x] `introduction`: TEXT、NOT NULL(キーワード検索の対象)
- [x] `created_at` / `updated_at`: TIMESTAMP、NULL 可

### 6.5 lawyer_practice_area(弁護士×取扱分野の中間テーブル)
- [x] `lawyer_id`: BIGINT UNSIGNED、NOT NULL、FK → lawyers.id、弁護士削除時 CASCADE
- [x] `practice_area_id`: BIGINT UNSIGNED、NOT NULL、FK → practice_areas.id
- [x] 複合主キー (`lawyer_id`, `practice_area_id`) で重複登録を防ぐ
- [x] 分野での絞り込み用 INDEX (`practice_area_id`, `lawyer_id`)
- [x] `created_at` / `updated_at` は持たない(定義書 6.5 に記載がないため)

### 確認
- [x] `docker compose exec api php artisan migrate:fresh` がエラーなく完了する
- [x] 5テーブルの `SHOW CREATE TABLE` で型・NULL・初期値・キー・インデックス・FK を定義書と照合する
- [x] テーブルとカラムの文字コードが utf8mb4 / utf8mb4_0900_ai_ci になっている(Laravel の初期値 utf8mb4_unicode_ci を `config/database.php` で変更)
- [x] `migrate:rollback` で全テーブルを削除できる(down が正しい)
