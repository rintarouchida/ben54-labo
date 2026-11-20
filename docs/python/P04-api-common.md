# P04 API共通(422の日本語化・レート制限)

- 参照: 要件定義書(FastAPI版)5.4節、7.4節、8.2節(共通)
- 前提: P01(P05 より先に進めると、P05 のテストで422の形をそのまま確かめられる)

## 概要
FastAPI の検証エラー(`RequestValidationError`)を、Laravel 版と同じ `{ message, errors }` の日本語に変換する。あわせて `/api/v1` 配下に1IPあたり毎分60回のレート制限をかける。

## 完了条件
- 422 のボディが5.4節の形(項目名は日本語、2件以上で ` (他N件のエラー)`)になる
- 61回目のリクエストが `{ "message": "Too Many Attempts." }` の429になり、`Retry-After` ヘッダーが付く
- 8.2節の共通のテストが成功する

## Todo
- [ ] `app/core/messages.py` に5.4節の項目名(都道府県 など)とエラー文の雛形を追加する
- [ ] `app/core/errors.py` に `ApiValidationError`(`errors: dict[str, list[str]]` を持つ例外)を追加し、`{ message, errors }` の422に変換するハンドラを登録する
- [ ] `message` の組み立て(最初のエラー文 + ` (他N件のエラー)`)を関数に分ける
- [ ] FastAPI の `RequestValidationError` も同じ形に変換する(P05 の `SearchLawyerRequest` を通らない検証エラーの受け皿)
- [ ] slowapi の `Limiter` を `create_app()` の中で作り、`/api/v1` 配下だけに毎分60回をかける(`/up` は除く)。`app.state.limiter` に置き、テストごとに新しいアプリで数え直せるようにする
- [ ] 429 のボディを `{ "message": "Too Many Attempts." }` にし、`Retry-After` を付ける
- [ ] Unit テスト: `message` の組み立て(1件、2件、4件)
- [ ] Feature テスト: 422 の形(1件、複数件、配列の要素 `practice_area_ids.0`)
- [ ] Feature テスト: 60回目までは200、61回目は429、別のIPは数えない
- [ ] Feature テスト: `/up` はレート制限の対象にならない
