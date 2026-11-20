"""テストデータの Factory(factory_boy。Laravel の Factory に当たる)

使い方:
    PrefectureFactory.create_batch(47)
    LawyerFactory(birth_date=date(1990, 4, 1), practice_areas=[area1, area2])

セッションは tests/conftest.py の db_session が `bind_session()` でテストごとに渡す。
作ったデータは flush されるので、id がすぐに使える。
"""

from datetime import date
from typing import Any

import factory
from dateutil.relativedelta import relativedelta
from factory.alchemy import SQLAlchemyModelFactory
from faker import Faker
from sqlalchemy.orm import Session

from app.data.names import FIRST_NAMES, LAST_NAMES
from app.data.practice_areas import PRACTICE_AREAS
from app.data.prefectures import PREFECTURES
from app.models import LawOffice, Lawyer, PracticeArea, Prefecture

fake = Faker("ja_JP")


class BaseFactory(SQLAlchemyModelFactory):  # type: ignore[misc]
    class Meta:
        abstract = True
        sqlalchemy_session_persistence = "flush"


def bind_session(session: Session) -> None:
    """全 Factory に、このテストのセッションを使わせる。重複させない値の記録も消す"""
    for factory_class in (PrefectureFactory, PracticeAreaFactory, LawOfficeFactory, LawyerFactory):
        factory_class._meta.sqlalchemy_session = session
    fake.unique.clear()


class PrefectureFactory(BaseFactory):
    """都道府県。id はテストの中で重複しない1〜47をランダムな順で選ぶ"""

    class Meta:
        model = Prefecture

    id = factory.LazyFunction(lambda: fake.unique.random_int(1, 47))
    name = factory.LazyAttribute(lambda o: PREFECTURES[o.id])


class PracticeAreaFactory(BaseFactory):
    """取扱分野。名前は12分野からテストの中で重複しないように選ぶ"""

    class Meta:
        model = PracticeArea

    name = factory.LazyFunction(lambda: fake.unique.random_element(tuple(PRACTICE_AREAS)))


class LawOfficeFactory(BaseFactory):
    class Meta:
        model = LawOffice

    prefecture = factory.SubFactory(PrefectureFactory)
    name = factory.LazyFunction(
        lambda: (
            fake.random_element(LAST_NAMES)[0]
            + fake.random_element(["", "総合", "綜合", "共同"])
            + "法律事務所"
        )
    )
    # 住所は都道府県名から書く
    address = factory.LazyAttribute(
        lambda o: o.prefecture.name + fake.city() + fake.street_address()
    )
    phone_number = factory.LazyFunction(lambda: fake.numerify("0#-####-####"))


def _birth_date() -> date:
    """25〜80歳の生年月日"""
    today = date.today()
    return fake.date_between(
        today - relativedelta(years=81) + relativedelta(days=1), today - relativedelta(years=25)
    )


class LawyerFactory(BaseFactory):
    """弁護士。取扱分野は `practice_areas=[...]` で渡す(省略すると分野なし)"""

    class Meta:
        model = Lawyer

    class Params:
        last = factory.LazyFunction(lambda: fake.random_element(LAST_NAMES))
        first = factory.LazyFunction(lambda: fake.random_element(FIRST_NAMES))

    law_office = factory.SubFactory(LawOfficeFactory)
    name = factory.LazyAttribute(lambda o: f"{o.last[0]} {o.first[0]}")
    name_kana = factory.LazyAttribute(lambda o: f"{o.last[1]} {o.first[1]}")
    birth_date = factory.LazyFunction(_birth_date)
    # 登録年は25歳になる年以降
    registered_year = factory.LazyAttribute(
        lambda o: fake.random_int(o.birth_date.year + 25, date.today().year)
    )
    case_count = factory.LazyFunction(lambda: fake.random_int(0, 500))
    career = factory.LazyAttribute(lambda o: f"{o.registered_year}年 弁護士登録")
    introduction = factory.LazyFunction(lambda: fake.text(max_nb_chars=120))
    practice_areas: Any = factory.LazyFunction(list)
