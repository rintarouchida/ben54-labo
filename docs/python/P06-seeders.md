# P06 Seeder(ダミーデータ1,000件)

- 参照: 要件定義書(FastAPI版)6.7節、8.3節(Seeder)
- 前提: P01

## 概要
開発用DBに、都道府県47件・分野12件・事務所300件・弁護士1,000件・中間テーブル約2,500件を入れる。

## 完了条件
- `docker compose exec api-py uv run python -m app.seeders --fresh` で全テーブルを作り直して投入できる
- 2回実行しても同じデータになる
- 8.3節の Seeder のテストが成功する

## Todo
- [ ] `app/seeders/` を作る(`prefecture_seeder.py` / `practice_area_seeder.py` / `law_office_seeder.py` / `lawyer_seeder.py`、全体を呼ぶ `run(session)`)
- [ ] Faker(ja_JP)と `random.Random` のシード値を固定する(定数 `FAKER_SEED`)
- [ ] 事務所の都道府県を人口で重み付けして選ぶ(`app/data/prefecture_profiles.py`。住所は「都道府県名 + 県庁所在地 + 番地」)
- [ ] 弁護士: 25〜80歳、登録年は25歳以降、実績数0〜500、氏名は `app/data/names.py` から
- [ ] 中間テーブル: 1人あたり1〜4分野。まとめて INSERT する(1行ずつ発行しない)
- [ ] `app/seeders/__main__.py`: `--fresh` で `alembic downgrade base` → `upgrade head` してから投入する
- [ ] テスト用の生成ルールを `tests/factories.py` と共有できるところは共有する(氏名の組み立てなど)
- [ ] Unit テスト: 件数(47 / 12 / 300 / 1,000)、全員が1分野以上、2回実行して同じデータ
- [ ] `backend-python/README.md` に投入手順を書く
