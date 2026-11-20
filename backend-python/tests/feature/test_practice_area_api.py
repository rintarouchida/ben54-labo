"""API-04: GET /api/v1/practice-areas(要件定義書 8.2節)"""

from fastapi.testclient import TestClient
from sqlalchemy.orm import Session

from tests.factories import PracticeAreaFactory


def test_practice_areas_一覧を取得する_登録件数分をID昇順で返す(
    client: TestClient, db_session: Session
) -> None:
    areas = PracticeAreaFactory.create_batch(12)

    response = client.get("/api/v1/practice-areas")

    assert response.status_code == 200
    assert response.json()["data"] == [
        {"id": a.id, "name": a.name} for a in sorted(areas, key=lambda a: a.id)
    ]


def test_practice_areas_マスターが0件_空配列を返す(client: TestClient) -> None:
    response = client.get("/api/v1/practice-areas")

    assert response.status_code == 200
    assert response.json() == {"data": []}
