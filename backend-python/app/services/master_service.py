"""マスター(都道府県・取扱分野)の取得"""

from collections.abc import Sequence
from typing import Annotated

from fastapi import Depends
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.database import get_session
from app.models import PracticeArea, Prefecture


class MasterService:
    def __init__(self, session: Session) -> None:
        self._session = session

    def prefectures(self) -> Sequence[Prefecture]:
        """都道府県を id の昇順で返す"""
        return self._session.scalars(select(Prefecture).order_by(Prefecture.id)).all()

    def practice_areas(self) -> Sequence[PracticeArea]:
        """取扱分野を id の昇順で返す"""
        return self._session.scalars(select(PracticeArea).order_by(PracticeArea.id)).all()


def get_master_service(session: Annotated[Session, Depends(get_session)]) -> MasterService:
    """ルーターに MasterService を渡す依存関数"""
    return MasterService(session)
