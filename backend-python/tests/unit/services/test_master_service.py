from sqlalchemy.orm import Session

from app.services.master_service import MasterService
from tests.factories import PracticeAreaFactory, PrefectureFactory


def test_prefectures_登録順がばらばら_id昇順で返す(db_session: Session) -> None:
    PrefectureFactory(id=13)
    PrefectureFactory(id=1)
    PrefectureFactory(id=47)

    prefectures = MasterService(db_session).prefectures()

    assert [p.id for p in prefectures] == [1, 13, 47]
    assert [p.name for p in prefectures] == ["北海道", "東京都", "沖縄県"]


def test_prefectures_0件_空のリストを返す(db_session: Session) -> None:
    assert MasterService(db_session).prefectures() == []


def test_practice_areas_登録した件数_id昇順で返す(db_session: Session) -> None:
    areas = PracticeAreaFactory.create_batch(5)

    result = MasterService(db_session).practice_areas()

    assert [a.id for a in result] == sorted(a.id for a in areas)


def test_practice_areas_0件_空のリストを返す(db_session: Session) -> None:
    assert MasterService(db_session).practice_areas() == []
