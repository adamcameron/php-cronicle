FROM php:8.4.10-fpm-bookworm

RUN ["apt-get", "update"]
RUN ["apt-get", "install", "-y", "zip", "unzip", "git", "vim"]

RUN echo "alias ll='ls -alF'" >> ~/.bashrc
RUN echo "alias cls='clear; printf \"\033[3J\"'" >> ~/.bashrc

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY usr/local/etc/php/conf.d/error_reporting.ini /usr/local/etc/php/conf.d/error_reporting.ini
COPY usr/local/etc/php/conf.d/app.ini /usr/local/etc/php/conf.d/app.ini
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

RUN apt-get install -y libicu-dev && docker-php-ext-configure intl && docker-php-ext-install intl

RUN [ \
    "apt-get", "install", "-y",  \
    "libz-dev", \
    "libzip-dev", \
    "libfcgi0ldbl" \
]
RUN docker-php-ext-configure zip && docker-php-ext-install zip
RUN docker-php-ext-configure pdo_mysql && docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure opcache && docker-php-ext-install opcache

RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY usr/local/etc/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN ["apt-get", "install", "-y", "symfony-cli"]

# need to use 777 as both php-fpm (www-data) and php-cli (root) will write to these directories
RUN mkdir -p /var/cache/symfony && chown www-data:www-data /var/cache/symfony && chmod 777 /var/cache/symfony
RUN mkdir -p /var/cache/symfony/dev && chown www-data:www-data /var/cache/symfony/dev && chmod 777 /var/cache/symfony/dev
RUN mkdir -p /var/log/symfony && chown www-data:www-data /var/log/symfony && chmod 777 /var/log/symfony

HEALTHCHECK \
    --start-period=30s \
    --interval=30s \
    --timeout=3s \
    --retries=3 \
    CMD SCRIPT_FILENAME=/var/www/bin/healthCheck.php REQUEST_METHOD=GET cgi-fcgi -bind -connect localhost:9000 | grep 'pong' || exit 1

WORKDIR /var/www
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN git config --global --add safe.directory /var/www

COPY --chmod=755 usr/local/bin/entrypoint.sh /usr/local/bin/
ENTRYPOINT ["entrypoint.sh"]

EXPOSE 9000
