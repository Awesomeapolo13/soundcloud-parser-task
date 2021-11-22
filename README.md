#Тестовое задание - парсер музыки

##Деплой

Добавить в файл /etc/hosts запись
```
127.0.0.1 music-parser
```

Для запуска проекта, необходимо выполнить команды (рекомендуется 
остановить локальные службы nginx и mysql, если такие имеются)
```shell
docker-compose build

docker-compose up -d
```

Затем зайти в контейнер php-7.4 и установить зависимости
```shell
docker exec -it php-7.4 bash

composer install
```
Выйти из контейнера, нажав Ctrl + D

Создать базу данных
```shell
docker-compose run --rm php-7.4 php bin/console doctrine:database:create
```

Выполнить миграции
```shell
docker-compose run --rm php-7.4 php bin/console doctrine:migrations:migrate
```

##Работа с проектом

Класс SoundCloudParser запускается консольной командой ExampleCommand. 
Для запуска в контейнере выполнить в терминале команду:
```shell
docker-compose run --rm php-7.4 php bin/console app:parse-example soundcloud_parse_url
```
где soundcloud_parse_url - url адрес soundcloud фомата https://soundcloud.com/dixxy-2
