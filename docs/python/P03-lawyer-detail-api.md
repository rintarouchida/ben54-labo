# P03 弁護士詳細API(API-02)

- 参照: 要件定義書(FastAPI版)F-14、F-16、5.3節、5.4節、7.5節、8.2節、8.3節(`Lawyer.age`、`LawyerSearchService.find`)
- 担当: 手動で実装する
- 前提: P01、P02(P02 の `masters.py` と `master_service.py` が見本になる)

## 概要
`GET /api/v1/lawyers/{id}` を作る。弁護士1件の全項目を、所属事務所・都道府県・取扱分野と一緒に返す。

Laravel 版で言うと、次の4つを作るチケット。

| Laravel 版 | このチケットで作るもの |
| --- | --- |
| `Lawyer` の `age` アクセサ | `Lawyer.age`(`@property`) |
| `LawyerSearchService::find()` | `LawyerSearchService.find()` |
| `LawyerDetailResource` | `LawyerDetailResponse`(Pydantic) |
| `LawyerController::show()` と `routes/api.php` | `app/routers/lawyers.py` |

## 完了条件
- 存在するIDで、5.3節の形の JSON を200で返す
- 存在しないID、数値でないID(`abc`)、`0` で `{ "message": "弁護士が見つかりません。" }` を404で返す
- 下の「テストケース」がすべて成功し、`ruff check` / `mypy` / `pytest --cov` が通る

## 処理の流れ(完成形)

```
GET /api/v1/lawyers/128
  → app/routers/lawyers.py  show_lawyer(lawyer_id=128)          入出力だけ
      → Depends(get_lawyer_search_service) で LawyerSearchService を受け取る
  → LawyerSearchService.find(128)
      → SELECT lawyers + 事務所・都道府県・分野を selectinload で事前に読み込む
      → 無ければ NotFoundError を投げる → app/core/errors.py が404に変換(P01 で作成済み)
  → LawyerDetailResponse.from_lawyer(lawyer)                   5.3節の形に詰める
  → DataResponse(data=...)                                     { "data": {...} } に包む
```

---

## Todo

各ステップの最後に、そのステップのテストを書いて通してから次へ進む。テストは `docker compose exec api-py uv run pytest tests/unit/models -q` のようにファイルやフォルダを指定すると早い。1件だけ動かすなら `-k 誕生日の当日` のように名前の一部を渡す。

### ステップ1: 年齢(`Lawyer.age`)

ファイル: `app/models/lawyer.py`

- [ ] `Lawyer` に `age` プロパティを追加する(`@property`、戻り値は `int`)
- [ ] 今日の日付は `date.today()` で取る(time-machine で固定できるのは、この呼び方)
- [ ] 計算: 「今年 − 生まれ年」から、今年の誕生日がまだ来ていなければ1を引く
- [ ] モジュールの先頭の docstring にある「`age` は P03 で追加する」を消す(絞り込み条件は P05 なので残す)

<details>
<summary>ヒント</summary>

- 「誕生日が来たか」は `(今日の月, 今日の日) >= (誕生月, 誕生日)` のようにタプルで比べると1行で書ける。Laravel 版も同じ書き方
- この書き方だと、2月29日生まれは、うるう年以外では3月1日に1つ増える(要件定義書 8.3節。Laravel 版と同じ)
- `@property` はDBのカラムではないので、SQLAlchemy の `mapped_column` は使わない

</details>

テスト: `tests/unit/models/test_lawyer_age.py`(新規)

- [ ] `test_age_誕生日の前日_まだ年を取らない`
- [ ] `test_age_誕生日の当日_年を取る`
- [ ] `test_age_誕生日の翌日_年を取っている`
- [ ] `test_age_2月29日生まれでうるう年以外の2月28日_まだ年を取らない`
- [ ] `test_age_2月29日生まれでうるう年以外の3月1日_年を取る`

<details>
<summary>ヒント</summary>

- 年齢の計算はDBを使わないので、`Lawyer(birth_date=date(1990, 4, 12))` のようにモデルを直接作れば足りる(Factory も `db_session` もいらない)
- 日付の固定は `with time_machine.travel(date(2026, 4, 11), tick=False):` の中で `lawyer.age` を読む
- 前日・当日・翌日は `@pytest.mark.parametrize` で1つのテストにまとめてもよい(その場合はテスト名を `test_age_誕生日の前後_当日から年を取る` のようにする)

</details>

### ステップ2: Service(`LawyerSearchService.find`)

ファイル: `app/services/lawyer_search_service.py`(新規)

- [ ] `LawyerSearchService` クラスを作る。コンストラクタで `Session` を受け取る(`MasterService` と同じ形)
- [ ] `find(self, lawyer_id: int) -> Lawyer` を作る
  - [ ] 事務所(`Lawyer.law_office`)、事務所の都道府県(`LawOffice.prefecture`)、取扱分野(`Lawyer.practice_areas`)を `selectinload` で事前に読み込む
  - [ ] 見つからなければ `app.core.errors.NotFoundError` を投げる
- [ ] 「関連を事前に読み込んだ `select(Lawyer)`」を作る処理は、private メソッド `_query_with_relations()` に分ける(P05 の `search()` でも使う。Laravel 版の `queryWithRelations()` と同じ)
- [ ] 依存関数 `get_lawyer_search_service(session)` を作る(`get_master_service` と同じ形)

<details>
<summary>ヒント</summary>

- 入れ子の読み込みは `selectinload(Lawyer.law_office).selectinload(LawOffice.prefecture)` のようにつなげる
- 1件取り出すには `self._session.scalars(文).one_or_none()`。`None` なら `raise NotFoundError`
- `_query_with_relations()` の戻り値の型は `Select[Lawyer]`(`from sqlalchemy import Select`)。SQLAlchemy 2.1 の書き方で、2.0 の記事によく出てくる `Select[tuple[Lawyer]]` は mypy でエラーになる
- モデルのリレーションは `lazy="raise"` にしてあるので、読み込みを忘れると、あとで `lawyer.law_office` を読んだときに `InvalidRequestError` になる。テストで気付ける
- 取扱分野の id 昇順は、モデルの `order_by="PracticeArea.id"` で付くので、ここでは書かなくてよい

</details>

テスト: `tests/unit/services/test_lawyer_search_service.py`(新規)

- [ ] `test_find_存在するID_事務所と都道府県と分野を読み込んで返す`(返ってきた弁護士の `law_office.prefecture.name` と `practice_areas` を読んでも例外にならない)
- [ ] `test_find_存在しないID_NotFoundErrorを投げる`

<details>
<summary>ヒント</summary>

- Factory で作ったオブジェクトはセッションに残っていて、関連もすでに入っている。本当に `find()` が読み込んだかを確かめるには、`find()` を呼ぶ前に `db_session.expunge_all()` でセッションを空にする(`tests/unit/models/test_models.py` の1つ目のテストが見本)
- 例外のテストは `with pytest.raises(NotFoundError):`
- 存在しないIDは、何も作っていない状態の `999` などでよい

</details>

### ステップ3: 出力の形(Schema)

ファイル: `app/schemas/lawyer.py`(新規)

- [ ] `LawOfficeDetailResponse`: `id` / `name` / `address` / `phone_number`
- [ ] `LawyerDetailResponse`: 5.3節の全項目(`id`、`name`、`name_kana`、`birth_date`、`age`、`registered_year`、`case_count`、`career`、`introduction`、`law_office`、`prefecture`、`practice_areas`)
  - [ ] `prefecture` と `practice_areas` には P02 の `PrefectureResponse` / `PracticeAreaResponse` を使う
  - [ ] `birth_date` は `date` 型にする(JSON では `"1974-04-12"` になる)
- [ ] モデルから作るクラスメソッド `from_lawyer(cls, lawyer: Lawyer) -> Self` を作る

<details>
<summary>ヒント</summary>

- `prefecture` は弁護士ではなく事務所の属性(`lawyer.law_office.prefecture`)なので、P02 のように `model_validate(lawyer)` だけでは作れない。`from_lawyer()` の中で、各項目を名前付き引数で渡して組み立てる
- `law_office` / `prefecture` / `practice_areas` の中身は `model_validate()` で作れる(それぞれ `from_attributes=True` にしておく)
- `age` はステップ1の `lawyer.age` を渡す
- `Self` は `from typing import Self`

</details>

テスト: Schema 単体のテストは書かなくてよい。ステップ4の Feature テストで全項目を確かめる。

### ステップ4: ルーター

ファイル: `app/routers/lawyers.py`(新規)、`app/main.py`

- [ ] `router = APIRouter(tags=["弁護士"])` を作る
- [ ] `GET /lawyers/{lawyer_id:int}` で `show_lawyer(lawyer_id: int, service: ...) -> DataResponse[LawyerDetailResponse]` を作る
  - [ ] 中身は「`service.find()` を呼ぶ → `from_lawyer()` で詰める → `DataResponse` で包む」だけにする(`NotFoundError` はルーターで捕まえない)
- [ ] `app/main.py` で `api.include_router(lawyers.router)` を追加する(`masters` の下)

<details>
<summary>ヒント</summary>

- パスの `{lawyer_id:int}` の `:int` は、数字だけのときにルートに一致させる指定。`abc` や `-1` はどのルートにも一致せず、P01 の例外ハンドラが `弁護士が見つかりません。` の404にする。`:int` を付けずに `lawyer_id: int` だけにすると、`abc` は FastAPI の422になってしまい、要件(404)を満たさない
- `0` や桁の大きすぎる数は `:int` に一致するが、DBに無いので `find()` が `NotFoundError` を投げて404になる
- 依存の受け取り方は `masters.py` の `Service = Annotated[MasterService, Depends(get_master_service)]` を真似る

</details>

テスト: `tests/feature/test_lawyer_detail_api.py`(新規)

- [ ] `test_lawyer_detail_存在するID_5_3節の全項目を返す`(JSON 全体を `==` で比べる。年齢があるので time-machine で日付を固定する)
- [ ] `test_lawyer_detail_複数の分野を持つ_id昇順で返す`
- [ ] `test_lawyer_detail_存在しないID_404と日本語のmessageを返す`
- [ ] `test_lawyer_detail_数値でないID_404と日本語のmessageを返す`(`abc`、`-1`、`1.5` を parametrize)
- [ ] `test_lawyer_detail_IDが0_404を返す`

<details>
<summary>ヒント</summary>

- `client` と `db_session` の両方をテストの引数に書く(`db_session` を書くと Factory が使える)
- 全項目のテストは、Factory に値を全部渡して作ると期待値が書きやすい。例: `LawyerFactory(name="山田 太郎", birth_date=date(1974, 4, 12), law_office=LawOfficeFactory(prefecture=PrefectureFactory(id=13), ...), practice_areas=[...])`
- 期待値の `id` は作ったオブジェクトから取る(`lawyer.id`、`lawyer.law_office.id`)。決め打ちにしない
- 分野の順番のテストは、`practice_areas=` に id の大きい順で渡して、レスポンスが小さい順になることを見る
- `client` で API を呼ぶと、その時点でテスト用のセッションが空になる(本番と同じく、API が必ずDBから読み直すようにするため。`tests/conftest.py` の `app`)。そのため、期待値に使う `lawyer.id` などは API を呼ぶ前に変数に取っておくと安全

</details>

### ステップ5: 仕上げ

- [ ] `docker compose exec api-py uv run ruff format .` と `ruff check .` が通る
- [ ] `docker compose exec api-py uv run mypy app tests` が通る
- [ ] `docker compose exec api-py uv run pytest --cov` が全件成功し、カバレッジが80%以上
- [ ] (任意)`backend-python/.env.example` を `.env` にコピーして `APP_DEBUG=true` にし、`docker compose restart api-py` のあと http://localhost:8001/docs から API-02 を試す
- [ ] このファイルの Todo をすべて `[x]` にし、`docs/python/README.md` の状態を「完了」にする

---

## 詰まったとき

| 症状 | よくある原因 |
| --- | --- |
| `InvalidRequestError: 'Lawyer.law_office' is not available due to lazy='raise'` | `selectinload` が足りない。都道府県は事務所の先にあるので、つなげて書く |
| `abc` で422が返る | パスに `:int` を付けていない |
| 404 のボディが `{"detail": "Not Found"}` | FastAPI の `HTTPException` を自分で投げている。`NotFoundError` を投げれば P01 のハンドラが変換する |
| テストで年齢が1つずれる | time-machine で日付を固定していない。Factory の生年月日は実行日を基準にランダムに決まる |
| mypy の `Incompatible return value type (got "Lawyer \| None", expected "Lawyer")` | `one_or_none()` の結果を `None` チェックせずに返している |
| mypy の `got "Select[Lawyer]", expected "Select[tuple[Lawyer]]"` | 戻り値の型を `Select[Lawyer]` にする(SQLAlchemy 2.1) |
| テストで `DetachedInstanceError` | API を呼んだあとに、Factory で作ったオブジェクトの読み込んでいない関連を読んだ。値は API を呼ぶ前に変数に取っておく |
| テストは通るのに、ブラウザの画面に出ない | フロントはまだ Laravel 版につながっている(切り替えは P07) |

終わったら「P03 終わった」と伝えてください。差分をレビューし、Laravel 版と同じレスポンスかを一緒に確認します。
