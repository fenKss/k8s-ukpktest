FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
            git \
            wget \
            nano \
            sudo \
            libzip-dev \
            libc-client-dev \
            libkrb5-dev \
            libpng-dev \
            libjpeg-dev \
            libwebp-dev \
            libfreetype6-dev \
            libkrb5-dev \
            libicu-dev \
            zlib1g-dev \
            zip \
            ffmpeg \
            libmemcached11 \
            libmemcachedutil2 \
            build-essential \
            libmemcached-dev \
            gnupg2 \
            libpq-dev \
            libpq5 \
            libz-dev \
            libyaml-0-2 \
            libxslt1-dev \
            libyaml-dev \
            mycli s3cmd

RUN docker-php-ext-configure gd \
    --with-webp=/usr/include/ \
    --with-freetype=/usr/include/ \
    --with-jpeg=/usr/include/
RUN docker-php-ext-install gd

RUN docker-php-ext-configure imap \
    --with-kerberos \
    --with-imap-ssl
RUN docker-php-ext-install imap

RUN docker-php-ext-configure zip && docker-php-ext-install zip

RUN docker-php-ext-configure intl && docker-php-ext-install intl

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install exif
RUN docker-php-ext-install fileinfo
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-configure xsl && docker-php-ext-install xsl && docker-php-ext-enable xsl
RUN docker-php-ext-install opcache

RUN pecl install yaml && docker-php-ext-enable yaml
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN pecl install memcached && docker-php-ext-enable memcached
RUN apt-get install librdkafka-dev -y;
RUN pecl install rdkafka && docker-php-ext-enable rdkafka
RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install apcu && docker-php-ext-enable apcu

COPY php.ini /usr/local/etc/php/conf.d/php.ini
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

RUN touch /var/log/fpm-php.www.log
RUN chmod 644 /var/log/fpm-php.www.log
RUN chgrp www-data /var/log/fpm-php.www.log
RUN chown www-data /var/log/fpm-php.www.log

RUN ln -sf /usr/share/zoneinfo/Europe/Moscow /etc/localtime

RUN addgroup  auth
RUN adduser --home /var/www/auth  --gecos "First Last,RoomNumber,WorkPhone,HomePhone" --disabled-password --ingroup auth auth

RUN echo "#auth without password" >> /etc/sudoers
RUN echo "auth ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers

COPY ./app /tmp/app

EXPOSE 9000
ENTRYPOINT ["php-fpm", "-R"]