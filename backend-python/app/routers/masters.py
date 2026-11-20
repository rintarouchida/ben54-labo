"""API-03 / API-04: マスターの一覧(要件定義書 5章)

ルーターは入出力だけを書く。取得は MasterService、整形は Schema に任せる。
"""

from typing import Annotated

from fastapi import APIRouter, Depends

from app.schemas.common import DataResponse
from app.schemas.practice_area import PracticeAreaResponse
from app.schemas.prefecture import PrefectureResponse
from app.services.master_service import MasterService, get_master_service

router = APIRouter(tags=["マスター"])

Service = Annotated[MasterService, Depends(get_master_service)]


@router.get("/prefectures")
def list_prefectures(service: Service) -> DataResponse[list[PrefectureResponse]]:
    """API-03: 都道府県の一覧"""
    return DataResponse(data=[PrefectureResponse.model_validate(p) for p in service.prefectures()])


@router.get("/practice-areas")
def list_practice_areas(service: Service) -> DataResponse[list[PracticeAreaResponse]]:
    """API-04: 取扱分野の一覧"""
    return DataResponse(
        data=[PracticeAreaResponse.model_validate(a) for a in service.practice_areas()]
    )
