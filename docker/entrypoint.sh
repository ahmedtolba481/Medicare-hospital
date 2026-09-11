#!/bin/sh

set -eu

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY must be set in the deployment environment." >&2
    exit 1
fi

apache_port="${PORT:-10000}"
sed -ri "s/^Listen [0-9]+/Listen ${apache_port}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:([0-9]+)>/<VirtualHost *:${apache_port}>/" /etc/apache2/sites-available/*.conf

mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs

if [ ! -e public/storage ]; then
    php artisan storage:link
fi

php artisan optimize

exec "$@"