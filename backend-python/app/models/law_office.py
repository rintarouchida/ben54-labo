"""法律事務所(要件定義書 6.3節)"""

from typing import TYPE_CHECKING

from sqlalchemy import ForeignKey, String
from sqlalchemy.dialects.mysql import BIGINT, TINYINT
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.models.base import TABLE_ARGS, Base, TimestampMixin

if TYPE_CHECKING:
    from app.models.lawyer import Lawyer
    from app.models.prefecture import Prefecture


class LawOffice(TimestampMixin, Base):
    __tablename__ = "law_offices"
    __table_args__ = TABLE_ARGS

    id: Mapped[int] = mapped_column(BIGINT(unsigned=True), primary_key=True)
    prefecture_id: Mapped[int] = mapped_column(
        TINYINT(unsigned=True), ForeignKey("prefectures.id"), index=True
    )
    name: Mapped[str] = mapped_column(String(100))
    address: Mapped[str] = mapped_column(String(255))
    phone_number: Mapped[str] = mapped_column(String(20))

    # lazy="raise": 事前読み込み(selectinload など)を忘れると例外にし、N+1 を防ぐ
    prefecture: Mapped["Prefecture"] = relationship(back_populates="law_offices", lazy="raise")
    lawyers: Mapped[list["Lawyer"]] = relationship(back_populates="law_office", lazy="raise")
