"""テストの共通設定

- テスト用DB(lawyer_search_py_test)の全テーブルを、テストの開始時に1回だけ Alembic で作り直す
- テストごとにトランザクションを開始し、終わったらロールバックする
  (Laravel の RefreshDatabase に当たる)
- Factory と API(TestClient)は、どちらも同じテスト用のセッションを使う
"""

from collections.abc import Iterator

import pytest
from alembic import command
from alembic.config import Config
from fastapi import FastAPI
from fastapi.testclient import TestClient
from sqlalchemy import Engine, text
from sqlalchemy.orm import Session

from app.core.database import create_db_engine, get_session
from app.main import create_app
from app.models import Base
from tests import factories


@pytest.fixture(scope="session")
def engine() -> Iterator[Engine]:
    test_engine = create_db_engine(testing=True)
    with test_engine.begin() as connection:
        # 前回のテストの残りを消してから、マイグレーションで作り直す
        Base.metadata.drop_all(connection)
        connection.execute(text("DROP TABLE IF EXISTS alembic_version"))
        config = Config("alembic.ini")
        config.attributes["connection"] = connection
        config.attributes["configure_logger"] = False
        command.upgrade(config, "head")
    yield test_engine
    test_engine.dispose()


@pytest.fixture
def db_session(engine: Engine) -> Iterator[Session]:
    """テスト1件分のセッション。テストの中で commit してもロールバックされる"""
    connection = engine.connect()
    transaction = connection.begin()
    session = Session(
        bind=connection, join_transaction_mode="create_savepoint", expire_on_commit=False
    )
    factories.bind_session(session)
    try:
        yield session
    finally:
        session.close()
        transaction.rollback()
        connection.close()


@pytest.fixture
def app(db_session: Session) -> FastAPI:
    test_app = create_app()

    def override_get_session() -> Session:
        # 本番ではリクエストごとに新しいセッションになる。それに合わせ、Factory で作って
        # セッションに残っているオブジェクトを切り離し、API が必ずDBから読み直すようにする
        db_session.expunge_all()
        return db_session

    test_app.dependency_overrides[get_session] = override_get_session
    return test_app


@pytest.fixture
def client(app: FastAPI) -> Iterator[TestClient]:
    """API を呼ぶクライアント

    予期しない例外はテストの失敗として表示する。500 のレスポンスを確かめたいテストでは
    `TestClient(app, raise_server_exceptions=False)` を自分で作る。
    """
    with TestClient(app) as test_client:
        yield test_client
