# P05 弁護士検索API(API-01)

- 参照: 要件定義書(FastAPI版)F-01〜F-12、5.1節、5.2節、6.6節、7.5節、8.2節、8.3節
- 前提: P03(`Lawyer.age`、`LawyerSearchService`、`_query_with_relations()`)、P04(422 の形)

## 概要
`GET /api/v1/lawyers` を作る。都道府県・取扱分野(OR)・キーワード(AND)で絞り込み、ソートして20件ずつ返す。

## 完了条件
- 5.2節の形(`data` / `links` / `meta`)で返す
- 5.1節の検証をすべて行い、5.4節のエラー文の422を返す
- 1,000件・全条件指定で500ms以内、SQLの発行数が件数によらず一定
- 8.2節の API-01 と、8.3節の関連するテストが成功する

## Todo

### 部品
- [ ] `app/enums/lawyer_sort_type.py`: `LawyerSortType(StrEnum)` と `order_columns()`(同値は必ず `id ASC`)
- [ ] `app/support/like_escaper.py`: `escape_like()`(`\` を最初にエスケープする)
- [ ] Unit テスト: `order_columns()` の3種類、定義外の文字列で `ValueError`
- [ ] Unit テスト: `escape_like()`(`%`、`_`、`\`、通常の文字、空文字)

### 絞り込み条件(Laravel のスコープ)
- [ ] `Lawyer.in_prefecture(stmt, prefecture_id)`: `Lawyer.law_office.has(LawOffice.prefecture_id == ...)`
- [ ] `Lawyer.with_any_practice_area(stmt, ids)`: `Lawyer.practice_areas.any(PracticeArea.id.in_(ids))`(EXISTS。JOIN しない)
- [ ] `Lawyer.match_keyword(stmt, keyword)`: 半角・全角の空白で分割し、語ごとに「名前・フリガナ・自己紹介・事務所名のどれかに含む」を AND でつなぐ。`like(..., escape="\\")`
- [ ] どれも `None`(分野は空リストも)なら絞り込まない
- [ ] Unit テスト: 各クラスメソッドが対象だけを絞り込む、`None` で絞り込まない
- [ ] `app/models/lawyer.py` の docstring から「P05 で追加する」を消す

### 入力検証
- [ ] `app/schemas/search_lawyer_request.py`: `SearchLawyerRequest`(検証済みの値: `prefecture_id: int | None`、`practice_area_ids: list[int]`、`keyword: str | None`、`sort: LawyerSortType`、`page: int`)
- [ ] 依存関数でクエリ文字列を読む(`practice_area_ids[]` の `[]` 付きの名前、空文字は未指定、`[]` なしの `practice_area_ids` は「配列でない」)
- [ ] 5.4節の表のとおり検証し、項目ごとに最初のエラーだけを集めて `ApiValidationError` を投げる。「存在すること」はDBで確認する
- [ ] Unit テスト: 5.4節の表の全行と、初期値(`sort` 省略で `case_count`、`page` 省略で1)

### Service・出力・ルーター
- [ ] `LawyerSearchService.search(prefecture_id, practice_area_ids, keyword, sort, page)`: 絞り込み → 総件数(`COUNT(*)`)→ 並べ替え → `LIMIT 20 OFFSET ...`。結果と総件数を持つ `Page` を返す
- [ ] `app/schemas/lawyer.py` に `LawyerResponse`(一覧の1件)と `LawyerCollectionResponse`(`data` / `links` / `meta`)を追加する
- [ ] `links` はリクエストのクエリを残した絶対URLにする(`page` だけを差し替える)
- [ ] `app/routers/lawyers.py` に `GET /lawyers` を追加する
- [ ] Unit テスト: `search()` の各条件を単独・組み合わせ、空文字・空白のみのキーワードと空リストの分野は条件なし
- [ ] Feature テスト: 8.2節の API-01 の全行(正常・異常)
- [ ] Feature テスト: SQL の発行数が件数によらず一定(`sqlalchemy.event` で `before_cursor_execute` を数える)
- [ ] 応答時間を測る(P06 の1,000件で、全条件指定 500ms 以内)
