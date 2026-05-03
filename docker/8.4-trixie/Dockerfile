FROM node:24-trixie-slim AS node-runtime
FROM composer:2 AS composer-runtime

FROM debian:trixie-slim AS base

LABEL maintainer="Taylor Otwell"

ARG WWWGROUP

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV LANG=C.UTF-8

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy     true;" >> /etc/apt/apt.conf.d/99custom

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        dnsutils \
        ffmpeg \
        git \
        gnupg \
        gosu \
        libcap2-bin \
        libpng-dev \
        librsvg2-bin \
        python3 \
        sqlite3 \
        unzip \
        zip \
        php8.4-bcmath \
        php8.4-cli \
        php8.4-curl \
        php8.4-dev \
        php8.4-fpm \
        php8.4-gd \
        php8.4-igbinary \
        php8.4-imagick \
        php8.4-intl \
        php8.4-ldap \
        php8.4-mbstring \
        php8.4-memcached \
        php8.4-mongodb \
        php8.4-msgpack \
        php8.4-mysql \
        php8.4-pgsql \
        php8.4-readline \
        php8.4-redis \
        php8.4-soap \
        php8.4-sqlite3 \
        php8.4-xml \
        php8.4-zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer-runtime /usr/bin/composer /usr/local/bin/composer
RUN ln -sf /usr/bin/php8.4 /usr/local/bin/php \
    && php -v \
    && composer --version

FROM base AS frontend-builder

COPY --from=node-runtime /usr/local /usr/local
COPY package.json package-lock.json /var/www/html/
RUN npm ci
COPY . /var/www/html
RUN npm run build

FROM base AS development

ARG MYSQL_CLIENT="default-mysql-client"
ARG WWWUSER
ARG WWWGROUP

ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php8.4 -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"
ENV PLAYWRIGHT_BROWSERS_PATH=0

COPY --from=node-runtime /usr/local /usr/local

RUN apt-get update \
    && apt-get install -y \
        $MYSQL_CLIENT \
        fswatch \
        nano \
        php8.4-pcov \
        php8.4-xdebug \
        postgresql-client \
        redis-tools \
        supervisor \
    && npx playwright install-deps \
    && rm -f /usr/local/bin/yarn /usr/local/bin/yarnpkg /usr/local/bin/pnpm /usr/local/bin/pnpx \
    && corepack enable \
    && corepack prepare yarn@stable --activate \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

RUN groupadd --force -g $WWWGROUP sail \
    && useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u $WWWUSER sail

RUN git config --global --add safe.directory /var/www/html

COPY docker/8.4-trixie/start-container /usr/local/bin/start-container
COPY docker/8.4-trixie/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/8.4-trixie/php.ini /etc/php/8.4/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]

FROM base AS production

ENV APP_ENV=production
ENV APP_DEBUG=false

RUN mkdir -p /run/php \
    && sed -i 's/listen = \/run\/php\/php8.4-fpm.sock/listen = 0.0.0.0:9000/' /etc/php/8.4/fpm/pool.d/www.conf

COPY composer.json composer.lock /var/www/html/
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . /var/www/html
COPY --from=frontend-builder /var/www/html/public/build /var/www/html/public/build
RUN rm -f bootstrap/cache/*.php \
    && composer dump-autoload --no-dev --optimize --no-interaction

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/8.4-trixie/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["/usr/sbin/php-fpm8.4", "-F"]

FROM nginx:alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=production /var/www/html/public /var/www/html/public
