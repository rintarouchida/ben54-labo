"""弁護士×取扱分野の中間テーブル(要件定義書 6.5節)

モデルのクラスは作らず、Table として定義する(relationship の secondary に渡す)。
"""

from sqlalchemy import Column, ForeignKey, Index, Table
from sqlalchemy.dialects.mysql import BIGINT

from app.models.base import TABLE_ARGS, Base

lawyer_practice_area = Table(
    "lawyer_practice_area",
    Base.metadata,
    Column(
        "lawyer_id",
        BIGINT(unsigned=True),
        ForeignKey("lawyers.id", ondelete="CASCADE"),
        primary_key=True,
    ),
    Column(
        "practice_area_id",
        BIGINT(unsigned=True),
        ForeignKey("practice_areas.id"),
        primary_key=True,
    ),
    # 分野での絞り込み用(複合主キーとは列の順番が逆)
    Index("lawyer_practice_area_practice_area_id_lawyer_id_index", "practice_area_id", "lawyer_id"),
    mysql_charset=TABLE_ARGS["mysql_charset"],
    mysql_collate=TABLE_ARGS["mysql_collate"],
)
