# 06 弁護士検索API(API-01)

- 参照: 要件定義書 F-01〜F-04, F-08〜F-12, 5.1, 5.2, 6.6, 7.3, 8.2, 8.3
- 時期: 1日目 午後

## 概要
`GET /api/v1/lawyers` で、都道府県・取扱分野(OR)・キーワードを AND で組み合わせて検索し、ソート・20件ずつのページネーションで返す。

## 完了条件
- 5.2節の形式(data / links / meta)で返す
- 8.2節の API-01 と 8.3節の Service / Enum のテストが成功する
- 1,000件・全条件指定で500ms以内、SQL発行数が件数によらず一定

## Todo
- [ ] `LawyerSortType` Enum(case_count / age / name、`orderColumns` で並び順を返す、同値は id ASC)
- [ ] `SearchLawyerRequest`(5.1節のバリデーション、sort の初期値 case_count)
- [ ] `LawyerSearchService::search`(スコープの組み合わせ、空文字・空白のみ・空配列は条件なし扱い)
- [ ] `with(['lawOffice.prefecture', 'practiceAreas'])` で N+1 を防ぐ
- [ ] 1ページ20件のページネーション(最終ページを超えたら空の data を200)
- [ ] LawyerResource(一覧用の項目)とコレクション
- [ ] LawyerController@index とルート
- [ ] Feature テスト(正常): 条件なし / 都道府県 / 分野2つで重複なし / キーワード4項目 / 空白区切り2語 / 3条件 AND
- [ ] Feature テスト(正常): sort 省略・case_count・age・name の並び順と同値時の id 昇順
- [ ] Feature テスト(正常): 2ページ目 / 最終ページ超え / 0件 / `%` を文字として検索
- [ ] Feature テスト(異常): prefecture_id(48、数値以外)/ practice_area_ids(配列以外、存在しない、重複)/ keyword 101文字(100文字は200)/ sort 定義外 / page(0、負数、文字列)で422
- [ ] Unit テスト: `LawyerSearchService::search`(単独・組み合わせ、空条件)
- [ ] Unit テスト: `LawyerSortType::orderColumns` と `tryFrom` の定義外で null
- [ ] SQL 発行数が件数によらず一定であることを確認する
