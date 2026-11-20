# チケット一覧(FastAPI版)

要件定義書 [ben54-labo-python.md](../../ben54-labo-python.md) の移行計画(9章)をチケットに分けたもの。各ファイルの Todo は完了したら `[ ]` を `[x]` にする。Laravel 版のチケットは [docs/README.md](../README.md)。

| No | チケット | 担当 | 状態 |
| --- | --- | --- | --- |
| P01 | [基盤構築](P01-foundation.md) | Claude | 完了 |
| P02 | [マスター取得API(API-03 / 04)](P02-master-api.md) | Claude | 完了 |
| P03 | [弁護士詳細API(API-02)](P03-lawyer-detail-api.md) | 手動で実装 | 未着手 |
| P04 | [API共通(422の日本語化・レート制限)](P04-api-common.md) | | 未着手 |
| P05 | [弁護士検索API(API-01)](P05-lawyer-search-api.md) | | 未着手 |
| P06 | [Seeder(ダミーデータ1,000件)](P06-seeders.md) | | 未着手 |
| P07 | [CI・フロントの切替・仕上げ](P07-ci-and-finish.md) | | 未着手 |

P03 は P01・P02 だけに依存し、P04・P05 より先に進められる。P05 は P03 が作る `Lawyer.age` と `LawyerSearchService` を使う。

## 各チケットで共通の確認コマンド

```bash
docker compose exec api-py uv run ruff format .     # 整形
docker compose exec api-py uv run ruff check .      # lint
docker compose exec api-py uv run mypy app tests    # 型チェック(strict)
docker compose exec api-py uv run pytest --cov      # 全テスト + カバレッジ(80%以上)
```
