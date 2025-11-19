#!/usr/bin/env bash
set -e

export PORT="${PORT}"
command -v envsubst >/dev/null 2>&1 || { echo "envsubst not found"; exit 1; }

envsubst '$PORT' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

php artisan config:clear || true
php artisan route:clear  || true
php artisan config:cache
php artisan route:cache

php-fpm -D
nginx -t -g 'pid /tmp/nginx.pid;'
nginx -g 'pid /tmp/nginx.pid; daemon off;'
