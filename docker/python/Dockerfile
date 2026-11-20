FROM python:3.13-slim

# uv(パッケージ管理)。バージョンを固定する
COPY --from=ghcr.io/astral-sh/uv:0.8.4 /uv /uvx /bin/

ENV TZ=Asia/Tokyo \
    PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    # 仮想環境はソースのマウント先の外(名前付きボリューム)に置く。ホスト(macOS)とは別物
    UV_PROJECT_ENVIRONMENT=/opt/venv \
    UV_LINK_MODE=copy \
    UV_CACHE_DIR=/opt/uv-cache

WORKDIR /app
