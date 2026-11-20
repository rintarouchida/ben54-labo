"""レスポンスに共通する形"""

from pydantic import BaseModel


class DataResponse[T](BaseModel):
    """`{ "data": ... }` の形(Laravel の API Resource と同じ包み方)"""

    data: T
