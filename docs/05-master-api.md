# 05 マスター取得API(API-03 / API-04)

- 参照: 要件定義書 F-19, 5章, 8.2
- 時期: 1日目 午後

## 概要
`GET /api/v1/prefectures` と `GET /api/v1/practice-areas` を作る。

## 完了条件
- `{ "data": [ { "id": 1, "name": "..." } ] }` の形で id 昇順に返す
- 8.2節の API-03 / API-04 のテストが成功する

## Todo
- [x] PrefectureController / PracticeAreaController(入出力のみ。`app/Http/Controllers/Api/V1/`)
- [x] API Resource で整形
- [x] `routes/api.php` にルートを追加
- [x] Feature テスト: 都道府県47件を id 昇順で返す
- [x] Feature テスト: 分野を登録件数分 id 昇順で返す
- [x] Feature テスト: マスターが0件のとき空配列を200で返す
- [x] 応答時間が200ms以内であることを確認する(実測 約10〜25ms)
