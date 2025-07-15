FROM php:8.2-fpm

COPY php.ini /usr/local/etc/php/

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN apt update \
  && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip \
  && docker-php-ext-install pdo_mysql zip

RUN apt-get update && apt-get install -y libicu-dev && docker-php-ext-install intl

RUN curl -sS https://getcomposer.org/installer | php && \
  mv composer.phar /usr/local/bin/composer && \
  composer self-update

# ユーザーとグループを作成
RUN groupadd -g ${GROUP_ID} appgroup || true && \
    useradd -m -u ${USER_ID} -g appgroup appuser || true

USER appuser

WORKDIR /var/www