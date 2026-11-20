"""アプリの設定(環境変数と .env から読み込む)"""

from functools import lru_cache

from pydantic_settings import BaseSettings, SettingsConfigDict
from sqlalchemy import URL


class Settings(BaseSettings):
    """環境変数の値。省略時は docker compose の環境に合わせた値になる"""

    model_config = SettingsConfigDict(env_file=".env", extra="ignore")

    app_debug: bool = False
    db_host: str = "db"
    db_port: int = 3306
    db_database: str = "lawyer_search_py"
    db_test_database: str = "lawyer_search_py_test"
    db_username: str = "fastapi"
    db_password: str = "secret"
    frontend_url: str = "http://localhost:5173"

    def database_url(self, *, testing: bool = False) -> URL:
        """SQLAlchemy の接続URL。testing=True ならテスト用DBを指す"""
        return URL.create(
            drivername="mysql+pymysql",
            username=self.db_username,
            password=self.db_password,
            host=self.db_host,
            port=self.db_port,
            database=self.db_test_database if testing else self.db_database,
            query={"charset": "utf8mb4"},
        )


@lru_cache
def get_settings() -> Settings:
    """設定を1回だけ読み込んで使い回す"""
    return Settings()
