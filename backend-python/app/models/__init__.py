"""モデル。ここで全モデルを読み込み、relationship の文字列の参照と Alembic の検出を解決する"""

from app.models.base import Base
from app.models.law_office import LawOffice
from app.models.lawyer import Lawyer
from app.models.lawyer_practice_area import lawyer_practice_area
from app.models.practice_area import PracticeArea
from app.models.prefecture import Prefecture

__all__ = ["Base", "LawOffice", "Lawyer", "PracticeArea", "Prefecture", "lawyer_practice_area"]
