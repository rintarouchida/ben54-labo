"""DB接続とセッション

1リクエストにつき1つのセッションを作り、ルーターには依存関数 `get_session` で渡す。
テストでは `app.dependency_overrides[get_session]` でテスト用のセッションに差し替える。
"""

from collections.abc import Generator

from sqlalchemy import Engine, create_engine
from sqlalchemy.orm import Session, sessionmaker

from app.core.config import get_settings


def create_db_engine(*, testing: bool = False) -> Engine:
    """MySQL への接続(コネクションプール)を作る"""
    return create_engine(
        get_settings().database_url(testing=testing),
        # MySQL が切ったコネクションを使い回さないよう、取り出すときに確認する
        pool_pre_ping=True,
    )


engine = create_db_engine()
SessionLocal = sessionmaker(bind=engine, autoflush=False, expire_on_commit=False)


def get_session() -> Generator[Session]:
    """リクエストの間だけ使うセッションを渡し、終わったら閉じる"""
    with SessionLocal() as session:
        yield session
