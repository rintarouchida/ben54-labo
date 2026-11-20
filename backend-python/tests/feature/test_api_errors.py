"""例外ハンドラ・CORS・ヘルスチェック(要件定義書 5.4節、7.4節)"""

import logging

import pytest
from fastapi import FastAPI
from fastapi.testclient import TestClient

from app.core.config import Settings
from app.core.errors import NotFoundError
from app.main import create_app


def test_up_ヘルスチェック_200を返す(client: TestClient) -> None:
    response = client.get("/up")

    assert response.status_code == 200
    assert response.json() == {"status": "ok"}


@pytest.mark.parametrize("path", ["/api/v1/undefined", "/api/v1/", "/undefined"])
def test_api_未定義のパス_404と日本語のmessageを返す(client: TestClient, path: str) -> None:
    response = client.get(path)

    assert response.status_code == 404
    assert response.json() == {"message": "弁護士が見つかりません。"}


def test_api_NotFoundErrorを投げる_404と日本語のmessageを返す(app: FastAPI) -> None:
    @app.get("/api/v1/test-not-found")
    def raise_not_found() -> None:
        raise NotFoundError

    response = TestClient(app).get("/api/v1/test-not-found")

    assert response.status_code == 404
    assert response.json() == {"message": "弁護士が見つかりません。"}


def test_api_許可されていないメソッド_messageだけの405を返す(client: TestClient) -> None:
    response = client.post("/up")

    assert response.status_code == 405
    assert response.json() == {"message": "Method Not Allowed"}
    assert response.headers["allow"] == "GET"


def test_api_予期しない例外_スタックトレースを返さず500を返す(
    app: FastAPI, caplog: pytest.LogCaptureFixture
) -> None:
    @app.get("/api/v1/test-error")
    def raise_error() -> None:
        raise RuntimeError("secret detail")

    with caplog.at_level(logging.ERROR):
        response = TestClient(app, raise_server_exceptions=False).get("/api/v1/test-error")

    assert response.status_code == 500
    assert response.json() == {"message": "サーバーエラーが発生しました。"}
    assert "secret detail" not in response.text
    # 例外はログに残る
    assert "secret detail" in caplog.text


def test_cors_許可したオリジン_ヘッダーを付ける(client: TestClient) -> None:
    response = client.get("/up", headers={"Origin": "http://localhost:5173"})

    assert response.headers["access-control-allow-origin"] == "http://localhost:5173"


def test_cors_許可していないオリジン_ヘッダーを付けない(client: TestClient) -> None:
    response = client.get("/up", headers={"Origin": "http://evil.example.com"})

    assert "access-control-allow-origin" not in response.headers


def test_docs_APP_DEBUGがFalse_OpenAPIの画面を公開しない(monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.setattr("app.main.get_settings", lambda: _settings(debug=False))

    response = TestClient(create_app()).get("/docs")

    assert response.status_code == 404


def test_docs_APP_DEBUGがTrue_OpenAPIの画面を公開する(monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.setattr("app.main.get_settings", lambda: _settings(debug=True))

    response = TestClient(create_app()).get("/docs")

    assert response.status_code == 200


def _settings(*, debug: bool) -> Settings:
    return Settings(_env_file=None, app_debug=debug)
