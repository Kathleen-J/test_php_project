## Важно
Запуск сервиса с накатом миграций, сидов и созданием администратора: `make firstStart`.
Откат миграций = `make down`.
Данные администратора:
- пароль - admin
- email - system@email.ru
API доступно по адресу `http://<host of service>:201/<prefix>/api/v1/`
Префиксы:
- auth
- users
- posts
- comments

## миграции
- Создание миграции: `vendor/bin/phinx create *MyNewMigration*`
- Накатывание миграции: `vendor/bin/phinx migrate`
- Откатывание одной миграции: `vendor/bin/phinx rollback`
- Откатывание всех миграций: `vendor/bin/phinx rollback -t 0`

## сиды
- Создание сида: `php vendor/bin/phinx seed:create *MyNewSeed*`
- Запустить сид: `php vendor/bin/phinx seed:run`
- Запустить конкретный сид: `php vendor/bin/phinx seed:run -s *MyNewSeed*`
- Запустить несколько конкретных сидов: `php vendor/bin/phinx seed:run -s *MyNewSeed* -s *MyNewSeed2* -s *MyNewSeed3*`

## server
- Настройка подключений к БД - в файле .env