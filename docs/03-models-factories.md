# 03 モデル・リレーション・Factory

- 参照: 要件定義書 6章, 6.6, 7.5, 8.3
- 時期: 1日目 午前

## 概要
各テーブルの Eloquent モデルとリレーション、検索用スコープ、年齢アクセサ、テスト用 Factory を作る。

## 完了条件
- リレーション・スコープ・アクセサが動き、8.3節の Unit テストが成功する

## Todo
- [x] Prefecture / PracticeArea / LawOffice / Lawyer モデルを作成(`declare(strict_types=1);` と型宣言)
- [x] リレーション(Prefecture→LawOffice→Lawyer、Lawyer⇔PracticeArea の多対多)
- [x] `Lawyer::age` アクセサ(生年月日から算出、保存しない。2月29日生まれはうるう年以外で3月1日に加算。タイムゾーンを Asia/Tokyo に変更)
- [x] スコープ `inPrefecture`(事務所の所在地で判定)
- [x] スコープ `withAnyPracticeArea`(`whereHas` + `whereIn` の EXISTS で重複なし)
- [x] スコープ `matchKeyword`(名前・フリガナ・事務所名・自己紹介の部分一致、半角/全角空白区切りで AND)
- [x] `escapeLike`(`%` `_` `\` のエスケープ。`App\Support\LikeEscaper::escapeLike`)
- [x] 各スコープは `null` を受けたら絞り込まない
- [x] 各モデルの Factory を作成(都道府県・分野の固定リストは `database/data/` に置き、Seeder と共用)
- [x] Unit テスト: `age`(誕生日の前日・当日・翌日、2月29日生まれ)
- [x] Unit テスト: 各スコープの正常系・`null` の場合
- [x] Unit テスト: `escapeLike`
- [x] Pint がテスト名を snake_case にしないよう `pint.json` で `php_unit_method_casing` を無効化し、`declare_strict_types` を有効化
