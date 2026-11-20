"""モデルの基底クラスと共通のカラム"""

from datetime import datetime

from sqlalchemy import MetaData, func
from sqlalchemy.dialects.mysql import TIMESTAMP
from sqlalchemy.orm import DeclarativeBase, Mapped, mapped_column

# 制約名の付け方を固定し、Alembic のマイグレーションと名前を揃える
NAMING_CONVENTION = {
    "ix": "%(table_name)s_%(column_0_N_name)s_index",
    "uq": "%(table_name)s_%(column_0_N_name)s_unique",
    "fk": "%(table_name)s_%(column_0_name)s_foreign",
    "pk": "pk_%(table_name)s",
}

# テーブルの文字コードと照合順序(要件定義書 7.1節)
TABLE_ARGS = {"mysql_charset": "utf8mb4", "mysql_collate": "utf8mb4_0900_ai_ci"}


class Base(DeclarativeBase):
    """すべてのモデルの基底クラス"""

    metadata = MetaData(naming_convention=NAMING_CONVENTION)


class TimestampMixin:
    """created_at / updated_at(Laravel の timestamps() に当たる)"""

    created_at: Mapped[datetime | None] = mapped_column(TIMESTAMP, default=func.now())
    updated_at: Mapped[datetime | None] = mapped_column(
        TIMESTAMP, default=func.now(), onupdate=func.now()
    )
