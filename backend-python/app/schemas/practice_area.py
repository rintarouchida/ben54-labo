"""取扱分野の出力の形(Laravel の PracticeAreaResource に当たる)"""

from pydantic import BaseModel, ConfigDict


class PracticeAreaResponse(BaseModel):
    # モデル(PracticeArea)の属性から作れるようにする
    model_config = ConfigDict(from_attributes=True)

    id: int
    name: str
