"""例外をAPIのエラーレスポンス `{ "message": ... }` に変換する(要件定義書 5.4節)

FastAPI の初期値の `{ "detail": ... }` は返さない。
検証エラー(422)の変換はチケット P04 で追加する。
"""

import logging
from http import HTTPStatus

from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
from starlette.exceptions import HTTPException as StarletteHTTPException

from app.core import messages

logger = logging.getLogger(__name__)


class NotFoundError(Exception):
    """探したデータが無い。404 `弁護士が見つかりません。` になる

    Service が投げ、ルーターでは捕まえない(Laravel の `findOrFail` に当たる)。
    """


def _not_found_response() -> JSONResponse:
    return JSONResponse({"message": messages.NOT_FOUND}, status_code=HTTPStatus.NOT_FOUND)


async def not_found_error_handler(request: Request, exc: Exception) -> JSONResponse:
    """Service が投げた NotFoundError を404にする"""
    return _not_found_response()


async def http_exception_handler(request: Request, exc: Exception) -> JSONResponse:
    """未定義のパス(404)や許可されていないメソッド(405)などを `{ message }` にする

    404 は理由によらず `弁護士が見つかりません。` にする(Laravel 版と同じ)。
    """
    assert isinstance(exc, StarletteHTTPException)
    if exc.status_code == HTTPStatus.NOT_FOUND:
        return _not_found_response()
    # detail は通常 "Method Not Allowed" のような理由句。文字列でなければ理由句にする
    message = exc.detail if isinstance(exc.detail, str) else HTTPStatus(exc.status_code).phrase
    return JSONResponse({"message": message}, status_code=exc.status_code, headers=exc.headers)


async def unhandled_exception_handler(request: Request, exc: Exception) -> JSONResponse:
    """予期しない例外を500にする。スタックトレースはログにだけ出し、レスポンスには含めない"""
    logger.exception("Unhandled exception: %s %s", request.method, request.url.path, exc_info=exc)
    return JSONResponse(
        {"message": messages.SERVER_ERROR}, status_code=HTTPStatus.INTERNAL_SERVER_ERROR
    )


def register_exception_handlers(app: FastAPI) -> None:
    """アプリに例外ハンドラを登録する"""
    app.add_exception_handler(NotFoundError, not_found_error_handler)
    app.add_exception_handler(StarletteHTTPException, http_exception_handler)
    app.add_exception_handler(Exception, unhandled_exception_handler)
