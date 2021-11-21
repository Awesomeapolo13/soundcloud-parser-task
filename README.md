#Тестовое задание - парсер музыки

##Деплой

Добавить в файл /etc/hosts запись
```
127.0.0.1 music-parser
```

Для запуска проекта, необходимо выполнить команды (рекомендуется 
остановить локальные службы nginx и mysql, если такие имеются)
```
docker-compose build

docker-compose up -d
```

Затем зайти в контейнер php-7.4 и установить зависимости
```
docker exec -it php-7.4 bash

composer install
```
Выйти из контейнера, нажав Ctrl + D

Создать базу данных
```
docker-compose run --rm php-7.4 php bin/console doctrine:database:create
```

Выполнить миграции
```
docker-compose run --rm php-7.4 php bin/console doctrine:migrations:migrate
```

