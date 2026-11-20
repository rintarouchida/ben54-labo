"""コンテナのヘルスチェック用(Laravel の /up に当たる)"""

from fastapi import APIRouter

router = APIRouter()


@router.get("/up", include_in_schema=False)
def up() -> dict[str, str]:
    return {"status": "ok"}
