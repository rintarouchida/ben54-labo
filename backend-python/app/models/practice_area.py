"""取扱分野マスター(要件定義書 6.2節)"""

from typing import TYPE_CHECKING

from sqlalchemy import String
from sqlalchemy.dialects.mysql import BIGINT
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.models.base import TABLE_ARGS, Base, TimestampMixin
from app.models.lawyer_practice_area import lawyer_practice_area

if TYPE_CHECKING:
    from app.models.lawyer import Lawyer


class PracticeArea(TimestampMixin, Base):
    __tablename__ = "practice_areas"
    __table_args__ = TABLE_ARGS

    id: Mapped[int] = mapped_column(BIGINT(unsigned=True), primary_key=True)
    name: Mapped[str] = mapped_column(String(50), unique=True)

    lawyers: Mapped[list["Lawyer"]] = relationship(
        secondary=lawyer_practice_area, back_populates="practice_areas", lazy="raise"
    )
