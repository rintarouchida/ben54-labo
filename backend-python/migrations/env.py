"""Alembic の実行環境

- 通常は app/core/config.py の開発用DBに接続する
- テストは `config.attributes["connection"]` に接続を渡して、テスト用DBに対して実行する
"""

from logging.config import fileConfig

from alembic import context
from sqlalchemy import Connection

from app.core.database import create_db_engine
from app.models import Base

config = context.config
if config.config_file_name is not None and config.attributes.get("configure_logger", True):
    fileConfig(config.config_file_name, disable_existing_loggers=False)

target_metadata = Base.metadata


def run_migrations(connection: Connection) -> None:
    context.configure(connection=connection, target_metadata=target_metadata, compare_type=True)
    with context.begin_transaction():
        context.run_migrations()


connection = config.attributes.get("connection")
if connection is not None:
    run_migrations(connection)
else:
    with create_db_engine().connect() as conn:
        run_migrations(conn)
