---
updated: 2026-05-04
covers: "config env defaults keys"
when_to_use: "cuando ajustes runtime o integraciones"
---

# Config and Env

## `config/app.php`

- Claves importantes:
  - `name` <- `APP_NAME`
  - `env` <- `APP_ENV`
  - `debug` <- `APP_DEBUG`
  - `url` <- `APP_URL`
  - `locale` <- `APP_LOCALE`
  - `fallback_locale` <- `APP_FALLBACK_LOCALE`
  - `faker_locale` <- `APP_FAKER_LOCALE`
- Valor por defecto notable: timezone sigue en `UTC`

## `config/auth.php`

- Claves importantes:
  - `defaults.guard` <- `AUTH_GUARD`
  - `defaults.passwords` <- `AUTH_PASSWORD_BROKER`
  - `providers.users.model` <- `AUTH_MODEL`
  - `passwords.users.table` <- `AUTH_PASSWORD_RESET_TOKEN_TABLE`
  - `passwords.users.expire` = 60
  - `passwords.users.throttle` = 60
  - `password_timeout` <- `AUTH_PASSWORD_TIMEOUT`

## `config/cache.php`

- Claves importantes:
  - `default` <- `CACHE_STORE`
  - `stores.database.table` <- `DB_CACHE_TABLE`
  - `stores.redis.connection` <- `REDIS_CACHE_CONNECTION`
  - `prefix` <- `CACHE_PREFIX` o slug de `APP_NAME`

## `config/database.php`

- Claves importantes:
  - `default` <- `DB_CONNECTION`
  - conexiones: `sqlite`, `mysql`, `mariadb`, `pgsql`, `sqlsrv`
  - `redis.default`, `redis.cache`
- En este proyecto el runtime detectado usa MySQL.

## `config/dompdf.php`

- Claves importantes:
  - `show_warnings` = false
  - `convert_entities` = true
  - `options.font_dir` / `font_cache` = `storage/fonts`
  - `options.chroot` = `base_path()`
  - `options.default_paper_size` = `letter`
  - `options.default_paper_orientation` = `portrait`
  - `options.dpi` = 96

## `config/filesystems.php`

- Claves importantes:
  - `default` <- `FILESYSTEM_DISK`
  - disk `public` usa `APP_URL/storage`
  - symbolic link `public/storage -> storage/app/public`

## `config/livewire.php`

- Claves importantes:
  - `component_locations`: `resources/views/components`, `resources/views/livewire`
  - `component_namespaces.layouts` -> `resources/views/layouts`
  - `class_namespace` -> `App\Livewire`
  - `class_path` -> `app/Livewire`
  - `view_path` -> `resources/views/livewire`
  - `navigate.show_progress_bar` = true

## `config/logging.php`

- Claves importantes:
  - `default` <- `LOG_CHANNEL`
  - `stack` <- `LOG_STACK`
  - `single.level` <- `LOG_LEVEL`
  - `daily.days` <- `LOG_DAILY_DAYS`
  - `slack.url` <- `LOG_SLACK_WEBHOOK_URL`

## `config/mail.php`

- Claves importantes:
  - `default` <- `MAIL_MAILER`
  - `smtp.host` <- `MAIL_HOST`
  - `smtp.port` <- `MAIL_PORT`
  - `from.address` <- `MAIL_FROM_ADDRESS`
  - `from.name` <- `MAIL_FROM_NAME`

## `config/permission.php`

- Claves importantes:
  - modelos de Spatie: `Permission`, `Role`
  - tablas: `roles`, `permissions`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`
  - `cache.expiration_time` = 24 hours
  - `teams` = false

## `config/purifier.php`

- Claves importantes:
  - `encoding` = UTF-8
  - `cachePath` = `storage/app/purifier`
  - `settings.default.HTML.Allowed` define HTML sanitizable para contenido editable

## `config/queue.php`

- Claves importantes:
  - `default` <- `QUEUE_CONNECTION`
  - driver por defecto en `.env.example`: `redis`
  - `failed.driver` <- `QUEUE_FAILED_DRIVER`

## `config/session.php`

- Claves importantes:
  - `driver` <- `SESSION_DRIVER`
  - `lifetime` <- `SESSION_LIFETIME`
  - `domain` <- `SESSION_DOMAIN`
  - `secure` <- `SESSION_SECURE_COOKIE`
  - `same_site` <- `SESSION_SAME_SITE`

## `config/services.php`

- Claves importantes:
  - `postmark.key` <- `POSTMARK_API_KEY`
  - `resend.key` <- `RESEND_API_KEY`
  - `ses.key/secret/region` <- AWS vars
  - `slack.notifications.bot_user_oauth_token` <- `SLACK_BOT_USER_OAUTH_TOKEN`

## `.env.example`

- Variables detectadas:
  - `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`
  - `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_FAKER_LOCALE`
  - `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_ENCRYPT`, `SESSION_PATH`, `SESSION_DOMAIN`
  - `QUEUE_CONNECTION`, `CACHE_STORE`, `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`
  - `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
  - `FILESYSTEM_DISK`, `BROADCAST_CONNECTION`
  - `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_USE_PATH_STYLE_ENDPOINT`

