from sqlalchemy import text

from app.core.database import get_session


def test_get_session_リクエストの終わり_セッションを閉じる() -> None:
    sessions = get_session()
    session = next(sessions)
    assert session.execute(text("SELECT 1")).scalar_one() == 1

    sessions.close()

    # 閉じたセッションはトランザクションを持たない
    assert not session.in_transaction()
