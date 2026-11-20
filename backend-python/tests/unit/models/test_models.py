import pytest
from sqlalchemy import delete, func, select
from sqlalchemy.exc import IntegrityError, InvalidRequestError
from sqlalchemy.orm import Session, selectinload

from app.models import LawOffice, Lawyer, lawyer_practice_area
from tests.factories import (
    LawOfficeFactory,
    LawyerFactory,
    PracticeAreaFactory,
    PrefectureFactory,
)


def test_lawyer_関連を事前に読み込む_事務所と都道府県と分野をたどれる(db_session: Session) -> None:
    prefecture = PrefectureFactory(id=13)
    areas = PracticeAreaFactory.create_batch(3)
    lawyer = LawyerFactory(
        law_office=LawOfficeFactory(prefecture=prefecture), practice_areas=list(reversed(areas))
    )
    db_session.expunge_all()

    loaded = db_session.scalars(
        select(Lawyer)
        .where(Lawyer.id == lawyer.id)
        .options(
            selectinload(Lawyer.law_office).selectinload(LawOffice.prefecture),
            selectinload(Lawyer.practice_areas),
        )
    ).one()

    assert loaded.law_office.prefecture.name == "東京都"
    # 取扱分野は登録順によらず id の昇順になる
    assert [area.id for area in loaded.practice_areas] == sorted(area.id for area in areas)


def test_lawyer_関連を読み込まずに参照_例外になりN1を防ぐ(db_session: Session) -> None:
    lawyer = LawyerFactory()
    db_session.expunge_all()

    loaded = db_session.get_one(Lawyer, lawyer.id)

    with pytest.raises(InvalidRequestError):
        _ = loaded.law_office


def test_lawyer_作成する_created_atとupdated_atが入る(db_session: Session) -> None:
    lawyer = LawyerFactory()
    db_session.refresh(lawyer)

    assert lawyer.created_at is not None
    assert lawyer.updated_at is not None


def test_lawyer_削除する_中間テーブルの行も消える(db_session: Session) -> None:
    lawyer = LawyerFactory(practice_areas=PracticeAreaFactory.create_batch(2))

    db_session.execute(delete(Lawyer).where(Lawyer.id == lawyer.id))

    count = db_session.execute(
        select(func.count())
        .select_from(lawyer_practice_area)
        .where(lawyer_practice_area.c.lawyer_id == lawyer.id)
    ).scalar_one()
    assert count == 0


def test_lawyer_practice_area_同じ組を2回登録_複合主キーで弾く(db_session: Session) -> None:
    lawyer = LawyerFactory()
    area = PracticeAreaFactory()
    row = {"lawyer_id": lawyer.id, "practice_area_id": area.id}
    db_session.execute(lawyer_practice_area.insert().values(row))

    with pytest.raises(IntegrityError):
        db_session.execute(lawyer_practice_area.insert().values(row))


def test_law_office_存在しない都道府県_外部キーで弾く(db_session: Session) -> None:
    db_session.add(LawOffice(prefecture_id=48, name="x", address="x", phone_number="x"))

    with pytest.raises(IntegrityError):
        db_session.flush()


def test_prefecture_factory_47件作る_IDが1から47で重複しない(db_session: Session) -> None:
    prefectures = PrefectureFactory.create_batch(47)

    assert sorted(p.id for p in prefectures) == list(range(1, 48))
