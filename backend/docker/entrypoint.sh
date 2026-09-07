#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
    case "${APP_ENV:-production}" in
        local|testing)
            APP_KEY="$(php artisan key:generate --show --no-interaction)"
            export APP_KEY
            ;;
        *)
            echo "APP_KEY must be a persistent secret outside local/testing." >&2
            exit 1
            ;;
    esac
fi

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
