-- 初回起動時のみ実行される。開発用DB(lawyer_search)は MYSQL_DATABASE で作成済み
CREATE DATABASE IF NOT EXISTS lawyer_search_test CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
GRANT ALL PRIVILEGES ON lawyer_search_test.* TO 'laravel'@'%';
FLUSH PRIVILEGES;
