import pytest

from app.core.config import Settings


def test_database_url_通常_開発用DBを指す() -> None:
    settings = Settings(_env_file=None)

    url = settings.database_url()

    assert url.drivername == "mysql+pymysql"
    assert url.host == "db"
    assert url.database == "lawyer_search_py"
    assert url.query == {"charset": "utf8mb4"}


def test_database_url_testingがTrue_テスト用DBを指す() -> None:
    settings = Settings(_env_file=None)

    assert settings.database_url(testing=True).database == "lawyer_search_py_test"


def test_settings_環境変数を指定_初期値より優先する(monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.setenv("DB_HOST", "127.0.0.1")
    monkeypatch.setenv("DB_PORT", "13306")
    monkeypatch.setenv("APP_DEBUG", "true")

    settings = Settings(_env_file=None)

    assert settings.database_url().host == "127.0.0.1"
    assert settings.database_url().port == 13306
    assert settings.app_debug is True


def test_settings_環境変数なし_本番向けにAPP_DEBUGはFalse(monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.delenv("APP_DEBUG", raising=False)

    assert Settings(_env_file=None).app_debug is False
