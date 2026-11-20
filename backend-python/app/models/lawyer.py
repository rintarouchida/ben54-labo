"""弁護士(要件定義書 6.4節)

年齢(`age`)と絞り込み条件(`in_prefecture` など)は、チケット P03 / P05 で追加する。
"""

from datetime import date
from typing import TYPE_CHECKING

from sqlalchemy import Date, ForeignKey, String, Text
from sqlalchemy.dialects.mysql import BIGINT, INTEGER, SMALLINT
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.models.base import TABLE_ARGS, Base, TimestampMixin
from app.models.lawyer_practice_area import lawyer_practice_area

if TYPE_CHECKING:
    from app.models.law_office import LawOffice
    from app.models.practice_area import PracticeArea


class Lawyer(TimestampMixin, Base):
    __tablename__ = "lawyers"
    __table_args__ = TABLE_ARGS

    id: Mapped[int] = mapped_column(BIGINT(unsigned=True), primary_key=True)
    law_office_id: Mapped[int] = mapped_column(
        BIGINT(unsigned=True), ForeignKey("law_offices.id"), index=True
    )
    name: Mapped[str] = mapped_column(String(50))
    name_kana: Mapped[str] = mapped_column(String(100), index=True)
    birth_date: Mapped[date] = mapped_column(Date, index=True)
    registered_year: Mapped[int] = mapped_column(SMALLINT(unsigned=True))
    case_count: Mapped[int] = mapped_column(
        INTEGER(unsigned=True), default=0, server_default="0", index=True
    )
    career: Mapped[str] = mapped_column(Text)
    introduction: Mapped[str] = mapped_column(Text)

    law_office: Mapped["LawOffice"] = relationship(back_populates="lawyers", lazy="raise")
    # 取扱分野は id の昇順で読み込む(要件定義書 5.2節)
    practice_areas: Mapped[list["PracticeArea"]] = relationship(
        secondary=lawyer_practice_area,
        back_populates="lawyers",
        order_by="PracticeArea.id",
        lazy="raise",
    )
