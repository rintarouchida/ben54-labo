# P02 マスター取得API(API-03 / API-04)

- 参照: 要件定義書(FastAPI版)F-19、5章、8.2節
- 担当: Claude
- 前提: P01

## 概要
`GET /api/v1/prefectures` と `GET /api/v1/practice-areas` を作る。P03 以降で同じ形(Service → Router → Schema)を使うので、その見本も兼ねる。

## 完了条件
- `{ "data": [ { "id": 1, "name": "..." } ] }` の形で id 昇順に返す
- 8.2節の API-03 / API-04 のテストが成功する
- Laravel 版と同じデータで、レスポンスの JSON が Laravel 版と一致する

## 処理の流れ

```
GET /api/v1/prefectures
  → app/routers/masters.py  list_prefectures()        入出力だけ
      → Depends(get_master_service) で MasterService を受け取る
          → Depends(get_session) で Session を受け取る
  → MasterService.prefectures()                        SELECT ... ORDER BY id
  → PrefectureResponse.model_validate(モデル)          from_attributes で属性から作る
  → DataResponse(data=[...])                           { "data": [...] } に包む
```

## Todo
- [x] `app/schemas/prefecture.py` / `practice_area.py`: 出力の形(`from_attributes=True`)
- [x] `app/services/master_service.py`: `MasterService.prefectures()` / `practice_areas()` と依存関数 `get_master_service`
- [x] `app/routers/masters.py`: 2つのエンドポイント(戻り値の型ヒントがレスポンスの形になる)
- [x] `app/main.py` で `/api/v1` のルーターに追加
- [x] Unit テスト: `MasterService` の2メソッド(id 昇順、0件で空リスト)
- [x] Feature テスト: 都道府県47件を id 昇順で返す、JISコードと名前の組
- [x] Feature テスト: 分野を登録件数分 id 昇順で返す
- [x] Feature テスト: マスターが0件のとき空配列を200で返す
- [x] Laravel 版(:8000)と FastAPI 版(:8001)のレスポンスを JSON として比べ、一致することを確認する
