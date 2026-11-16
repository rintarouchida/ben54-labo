# 10 フロントエンド基盤

- 参照: 要件定義書 4章, 7.1, 7.5
- 時期: 2日目 午前

## 概要
画面実装の土台(ルーティング・API クライアント・型定義・ディレクトリ構成)を作る。

## 完了条件
- `/`、`/lawyers/:id`、`*` のルートが表示を切り替えられる
- API から都道府県・分野を取得して表示できる

## Todo
- [x] `src/pages` `src/components` `src/api` `src/types` を作る
- [x] React Router 7 で SCR-01 / SCR-02 / SCR-03 のルートを定義
- [x] TanStack Query の QueryClient を設定
- [x] axios クライアント(ベースURL `/api/v1`)
- [x] API レスポンスの型定義(一覧・詳細・マスター・エラー)
- [x] API 呼び出し関数とフック(lawyers / lawyer / prefectures / practiceAreas)
- [x] 共通レイアウト(ヘッダー)
