1. Скопировать .env из .env.example

2. В корне папки с проектом нужно создать auth.json файл с:
```
{
    "http-basic": {
        "nova.laravel.com": {
            "username": "alexladweb@gmail.com",
            "password": "vxeLB5YEZO8gRpiL17QkmgHyu0BRUZlbw7zFDu6CvLITmk3YBE"
        }
    }
}
```

3. Потом запустить:
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs
```

4. Добавить алиасы для команд:
```
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
alias nollo-restart='sail down && sail up -d'
alias nollo-docker-remove-all='sail down --rmi all -v'
alias nollo-migrate-fresh='sail php artisan migrate:fresh'
```

Рабочий процесс:
```
# запустить
sail up -d

# миграции и сиды тестовых данных
sail artisan migrate:fresh --seed

# остановить
sail down
```

Login в Nova:
http://localhost/nova/login
```
admin@admin.com
123456
```

Команды:
```
sail composer ....
sail tinker ...
sail artisan ...
```
