FROM ubuntu:26.04 AS base

LABEL maintainer="Taylor Otwell"
ARG WWWGROUP
WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV LANG=C.UTF-8

# --- PARCHE DE ENRUTAMIENTO (ESPEJOS US) ---
# Fuerza el uso de los servidores de EE.UU. para evitar cortes de conexión 
# o cuellos de botella con los espejos locales de Ubuntu.
RUN sed -i 's/archive.ubuntu.com/us.archive.ubuntu.com/g' /etc/apt/sources.list.d/ubuntu.sources
# -------------------------------------------

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

RUN apt-get update -o APT::Update::Error-Mode=any \
    && apt-get install -y --no-install-recommends libgd3 php8.5-cli php8.5-dev php8.5-pgsql php8.5-sqlite3 php8.5-gd php8.5-curl php-mongodb php8.5-mysql php8.5-mbstring php8.5-xml php8.5-zip php8.5-bcmath php8.5-soap php8.5-intl php8.5-readline php8.5-ldap php-msgpack php-igbinary php-redis php-memcached php-pcov php-imagick \
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

ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php8.5 -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"
ENV PLAYWRIGHT_BROWSERS_PATH=0

RUN apt-get update -o APT::Update::Error-Mode=any \
    && apt-get install -y --no-install-recommends php8.5-xdebug supervisor $MYSQL_CLIENT postgresql-client \
    && curl --fail --show-error --location --retry 5 --retry-delay 2 --connect-timeout 30 https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update -o APT::Update::Error-Mode=any && apt-get install -y --no-install-recommends nodejs \
    && npm install -g npm pnpm bun && npx playwright install-deps && corepack enable && corepack prepare yarn@stable --activate \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.5

# --- SECCION DE RESCATE DE USUARIO ---
RUN touch /var/mail/ubuntu && chown ubuntu /var/mail/ubuntu && userdel -r ubuntu ; \
    groupadd --force -g $WWWGROUP sail && \
    useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u $WWWUSER sail
# -------------------------------------

RUN git config --global --add safe.directory /var/www/html

COPY docker/8.5/start-container /usr/local/bin/start-container
COPY docker/8.5/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/8.5/php.ini /etc/php/8.5/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80/tcp
ENTRYPOINT ["start-container"]

FROM base AS production
ENV APP_ENV=production
ENV APP_DEBUG=false

RUN apt-get update -o APT::Update::Error-Mode=any && apt-get install -y --no-install-recommends php8.5-fpm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /run/php && \
    sed -i 's/listen = \/run\/php\/php8.5-fpm.sock/listen = 0.0.0.0:9000/' /etc/php/8.5/fpm/pool.d/www.conf

COPY . /var/www/html
COPY --from=frontend-builder /var/www/html/public/build /var/www/html/public/build

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/8.5/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["/usr/sbin/php-fpm8.5", "-F"]