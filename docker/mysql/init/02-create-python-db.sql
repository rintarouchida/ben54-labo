-- FastAPI 版(backend-python)の開発用DBとテスト用DB。初回起動時のみ実行される
-- 既にボリュームがある環境では backend-python/README.md の手順で手動実行する
CREATE DATABASE IF NOT EXISTS lawyer_search_py CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
CREATE DATABASE IF NOT EXISTS lawyer_search_py_test CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
CREATE USER IF NOT EXISTS 'fastapi'@'%' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON lawyer_search_py.* TO 'fastapi'@'%';
GRANT ALL PRIVILEGES ON lawyer_search_py_test.* TO 'fastapi'@'%';
FLUSH PRIVILEGES;
