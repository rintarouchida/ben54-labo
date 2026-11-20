# P07 CI・フロントの切替・仕上げ

- 参照: 要件定義書(FastAPI版)0章、7.2節、7.3節、8章、9章
- 前提: P01〜P06

## 概要
CI で FastAPI 版を検査し、フロントを FastAPI 版につないで画面がすべて動くことを確かめる。

## 完了条件
- GitHub Actions で Ruff(整形・lint)・mypy・pytest(カバレッジ80%以上)が通る
- `API_PROXY_TARGET=http://api-py:8001` で SCR-01〜03 が Laravel 版と同じように動く

## Todo
- [ ] `.github/workflows/ci.yml` に `backend-python` ジョブを追加する(`docker compose up -d --wait db` → uv → `ruff format --check` / `ruff check` / `mypy` / `pytest --cov`)
- [ ] 初回起動のボリュームで `02-create-python-db.sql` が実行され、CI でテスト用DBが作られることを確認する
- [ ] Laravel 版と FastAPI 版に同じ条件で API-01〜04 を投げ、JSON の形・ステータスコード・エラー文を比べるスクリプトを書く(データは Seeder が違うので、形とエラーを比べる)
- [ ] `API_PROXY_TARGET=http://api-py:8001 docker compose up -d front` に切り替え、検索・ソート・ページ切替・詳細・404・422・500 の表示をブラウザで確認する
- [ ] 性能: API-01 が500ms以内、API-02〜04 が200ms以内(7.3節)
- [ ] `README.md`(ルート)に FastAPI 版の起動方法を追記する
- [ ] 要件定義書(FastAPI版)9.1節の未確定事項をユーザーに確認する(Laravel 版を消すか)
