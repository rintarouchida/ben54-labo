"""API-03: GET /api/v1/prefectures(要件定義書 8.2節)"""

from fastapi.testclient import TestClient
from sqlalchemy.orm import Session

from tests.factories import PrefectureFactory


def test_prefectures_一覧を取得する_47件をID昇順で返す(
    client: TestClient, db_session: Session
) -> None:
    # Factory はランダムな順で ID を作るため、登録順と ID 順が一致しない
    PrefectureFactory.create_batch(47)

    response = client.get("/api/v1/prefectures")

    assert response.status_code == 200
    data = response.json()["data"]
    assert [p["id"] for p in data] == list(range(1, 48))
    assert all(set(p) == {"id", "name"} for p in data)


def test_prefectures_一覧を取得する_JISコードと名前の組を返す(
    client: TestClient, db_session: Session
) -> None:
    PrefectureFactory.create_batch(47)

    data = client.get("/api/v1/prefectures").json()["data"]

    assert data[0] == {"id": 1, "name": "北海道"}
    assert data[12] == {"id": 13, "name": "東京都"}
    assert data[46] == {"id": 47, "name": "沖縄県"}


def test_prefectures_マスターが0件_空配列を返す(client: TestClient) -> None:
    response = client.get("/api/v1/prefectures")

    assert response.status_code == 200
    assert response.json() == {"data": []}
