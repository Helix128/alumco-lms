FROM ubuntu:24.04 AS base

LABEL maintainer="Taylor Otwell"
ARG WWWGROUP
WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV LANG=C.UTF-8

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy     true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::Retries \"5\";" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::Timeout \"30\";" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::https::Timeout \"30\";" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::ForceIPv4 \"true\";" >> /etc/apt/apt.conf.d/99custom

RUN apt-get update -o APT::Update::Error-Mode=any \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y --no-install-recommends gnupg gosu curl ca-certificates zip unzip git sqlite3 libcap2-bin libpng-dev python3 dnsutils librsvg2-bin fswatch ffmpeg nano \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN curl --fail --show-error --location --retry 5 --retry-delay 2 --connect-timeout 30 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list

RUN apt-get update -o APT::Update::Error-Mode=any \
    && apt-get install -y --no-install-recommends libgd3 php8.4-cli php8.4-dev php8.4-pgsql php8.4-sqlite3 php8.4-gd php8.4-curl php8.4-mongodb php8.4-imap php8.4-mysql php8.4-mbstring php8.4-xml php8.4-zip php8.4-bcmath php8.4-soap php8.4-intl php8.4-readline php8.4-ldap php8.4-msgpack php8.4-igbinary php8.4-redis php8.4-memcached php8.4-pcov php8.4-imagick \
    && curl --fail --show-error --location --retry 5 --retry-delay 2 --connect-timeout 30 https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

FROM base AS frontend-builder
ARG NODE_VERSION=24
COPY . /var/www/html
RUN curl --fail --show-error --location --retry 5 --retry-delay 2 --connect-timeout 30 https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update -o APT::Update::Error-Mode=any && apt-get install -y nodejs \
    && npm install && npm run build

FROM base AS development
ARG NODE_VERSION=24
ARG MYSQL_CLIENT="mysql-client"
ARG POSTGRES_VERSION=16
ARG WWWUSER
ARG WWWGROUP

ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"
ENV PLAYWRIGHT_BROWSERS_PATH=0

RUN apt-get update -o APT::Update::Error-Mode=any \
    && apt-get install -y --no-install-recommends php8.4-xdebug supervisor $MYSQL_CLIENT postgresql-client-$POSTGRES_VERSION \
    && curl --fail --show-error --location --retry 5 --retry-delay 2 --connect-timeout 30 https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update -o APT::Update::Error-Mode=any && apt-get install -y --no-install-recommends nodejs \
    && npm install -g npm pnpm bun && npx playwright install-deps && corepack enable && corepack prepare yarn@stable --activate \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

# --- SECCION DE RESCATE DE USUARIO ---
RUN touch /var/mail/ubuntu && chown ubuntu /var/mail/ubuntu && userdel -r ubuntu ; \
    groupadd --force -g $WWWGROUP sail && \
    useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u $WWWUSER sail
# -------------------------------------

RUN git config --global --add safe.directory /var/www/html

COPY docker/8.4/start-container /usr/local/bin/start-container
COPY docker/8.4/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/8.4/php.ini /etc/php/8.4/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80/tcp
ENTRYPOINT ["start-container"]

FROM base AS production
ENV APP_ENV=production
ENV APP_DEBUG=false

RUN apt-get update -o APT::Update::Error-Mode=any && apt-get install -y --no-install-recommends php8.4-fpm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /run/php && \
    sed -i 's/listen = \/run\/php\/php8.4-fpm.sock/listen = 0.0.0.0:9000/' /etc/php/8.4/fpm/pool.d/www.conf

COPY . /var/www/html
COPY --from=frontend-builder /var/www/html/public/build /var/www/html/public/build

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/8.4/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["/usr/sbin/php-fpm8.4", "-F"]
