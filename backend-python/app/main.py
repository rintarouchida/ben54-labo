"""FastAPI アプリの組み立て

`uvicorn app.main:app` で起動する。テストでは `create_app()` で毎回新しいアプリを作る。
"""

from fastapi import APIRouter, FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.core.config import get_settings
from app.core.errors import register_exception_handlers
from app.routers import health

API_PREFIX = "/api/v1"


def create_app() -> FastAPI:
    settings = get_settings()
    app = FastAPI(
        title="弁護士検索API",
        version="1.0.0",
        # OpenAPI の画面は開発時だけ公開する(要件定義書 7.4節)
        docs_url="/docs" if settings.app_debug else None,
        redoc_url=None,
        openapi_url="/openapi.json" if settings.app_debug else None,
    )

    app.add_middleware(
        CORSMiddleware,
        allow_origins=[settings.frontend_url],
        allow_methods=["GET"],
        allow_headers=["*"],
    )
    register_exception_handlers(app)

    # /api/v1 配下のルーター。チケットごとにここへ足していく
    # (include_router はその時点のルートを写すため、api への追加は app に含める前に書く)
    api = APIRouter(prefix=API_PREFIX)
    app.include_router(api)

    app.include_router(health.router)
    return app


app = create_app()
