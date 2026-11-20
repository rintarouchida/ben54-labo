"""都道府県マスター(要件定義書 6.1節)"""

from typing import TYPE_CHECKING

from sqlalchemy import String
from sqlalchemy.dialects.mysql import TINYINT
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.models.base import TABLE_ARGS, Base, TimestampMixin

if TYPE_CHECKING:
    from app.models.law_office import LawOffice


class Prefecture(TimestampMixin, Base):
    __tablename__ = "prefectures"
    __table_args__ = TABLE_ARGS

    # JISの都道府県コード(1〜47)。自動採番しない
    id: Mapped[int] = mapped_column(TINYINT(unsigned=True), primary_key=True, autoincrement=False)
    name: Mapped[str] = mapped_column(String(10), unique=True)

    law_offices: Mapped[list["LawOffice"]] = relationship(back_populates="prefecture", lazy="raise")
