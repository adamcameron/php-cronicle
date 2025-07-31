# php-cronicle
Investigating Cronicle

## Notes

One must create some files containing env var values that we don't want
in source control due to the sensitive nature of the values.

* `docker/mariadb/mariadb_password_file.private`
* `docker/mariadb/mariadb_root_password_file.private`
* `docker/php/appEnvVars.private`

The MariaDB ones should contain only the password for the DB user the app users;
and the root user used to create the DB, respectively.

`appEnvVars.private` should have a `[name]=[value]` pair for the following env vars:

```bash
APP_SECRET=[any value really]
MARIADB_PASSWORD=[same as the value in mariadb_password_file.private]
DATABASE_URL=[using same MARIADB values from other settings]
```

## Building for dev

```bash
# from the root of the project

docker compose -f docker/docker-compose.yml down
docker compose -f docker/docker-compose.yml build
docker compose -f docker/docker-compose.yml up --detach

# verify stability
docker container ls --format "table {{.Names}}\t{{.Status}}"
php       Up About an hour (healthy)
nginx     Up About an hour (healthy)
mariadb   Up About an hour (healthy)

docker exec -u www-data php composer test-all
./composer.json is valid
PHPUnit 12.2.9 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.4.10 with Xdebug 3.4.5
Configuration: /var/www/phpunit.xml.dist

Time: 00:02.270, Memory: 28.00 MB

OK (10 tests, 25 assertions)

Generating code coverage report in HTML format ... done [00:00.006]
```
