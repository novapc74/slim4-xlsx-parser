# Тестовое задание на позицию backend-разработчик
Необходимо создать функционал для подгрузки excel файла (формат .xlsx) через API, для дальнейшей обработки и сохранения данных в БД.
Требования php >= 8.1, slim 4, БД mariadb, для http запросов использовать guzzlehttp/guzzle, для работы с БД illuminate/database.
Для логирования monolog/monolog. Все необходимое уже предустановлено в текущем репозитории, есть docker для поднятия сервера со всем необходимым для выполнения задачи.

***
#### В .env добавить путь к файлу
```angular2html
XLSX_FILE_NAME="var/excel/..."
```
***
#### Создание таблиц в базе данных
```sql
CREATE TABLE ads_campaigns (
    id BIGINT UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE ads_ad_sets (
    id BIGINT UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE ads (
    id BIGINT UNIQUE NOT NULL,
    updated_at DATETIME,
    credit FLOAT,
    name VARCHAR(255) NOT NULL,
    display_count BIGINT,
    click_count BIGINT,
    company_id bigint REFERENCES ads_campaigns (id),
    group_id bigint REFERENCES ads_ad_sets (id),
    UNIQUE (id, updated_at)
);
```
***
#### Запуск приложения и команды:
```makefile
make init

make parse-multiply // множественная вставка
make parse-single // последовательная вставка
```



