from alembic.autogenerate import compare_metadata
from alembic.migration import MigrationContext
from sqlalchemy import Engine, inspect

from app.models import Base


def test_migrations_最新まで適用_モデルの定義と差がない(engine: Engine) -> None:
    with engine.connect() as connection:
        context = MigrationContext.configure(connection, opts={"compare_type": True})

        assert compare_metadata(context, Base.metadata) == []


def test_migrations_最新まで適用_5つのテーブルがある(engine: Engine) -> None:
    tables = set(inspect(engine).get_table_names())

    assert tables >= {
        "prefectures",
        "practice_areas",
        "law_offices",
        "lawyers",
        "lawyer_practice_area",
    }


def test_migrations_テーブルの照合順序_utf8mb4_0900_ai_ciになる(engine: Engine) -> None:
    options = inspect(engine).get_table_options("lawyers")

    assert options["mysql_collate"] == "utf8mb4_0900_ai_ci"
