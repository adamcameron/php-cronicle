# symfony-scheduler
Investigating Symfony Scheduler

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
MAILER_DSN=[SMTP connection string for fake-smtp-server]
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

## SMTP Server

The development environment includes a fake SMTP server for testing email functionality:

* **Web UI**: http://localhost:1080 - View captured emails
* **API**: http://localhost:1080/api/emails - Programmatic access for integration tests
* **SMTP**: localhost:25 - Where applications send emails

The fake SMTP server captures all outgoing emails instead of actually sending them, making it perfect for development and testing.

To delete all emails:
```bash
curl -X DELETE http://localhost:1080/api/emails
```

## Running the worker

```bash
docker exec php symfony run -d --watch=/tmp/symfony/schedule-last-updated.dat php bin/console messenger:consume
```

### Killing the worker

```bash
# find out its PID
docker exec php symfony server:status
Local Web Server
    Not Running

Workers
    PID 2385: php bin/console messenger:consume (watching /tmp/symfony/schedule-last-updated.dat/)

Environment Variables
    None
    
# kill  it  
docker exec php bash -c "kill 2385"
```

It's worth noting that that file path is specified as `SCHEDULE_RESTART_FILE` in the docker-compose.yml file.
