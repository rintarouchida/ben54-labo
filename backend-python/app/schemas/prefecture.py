"""都道府県の出力の形(Laravel の PrefectureResource に当たる)"""

from pydantic import BaseModel, ConfigDict


class PrefectureResponse(BaseModel):
    # モデル(Prefecture)の属性から作れるようにする
    model_config = ConfigDict(from_attributes=True)

    id: int
    name: str
