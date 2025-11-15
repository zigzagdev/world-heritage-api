FROM composer:2.6 AS vendor
WORKDIR /app
COPY src/ .
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
 && composer dump-autoload -o

FROM php:8.2-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx ca-certificates curl tzdata gettext-base \
    git unzip \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev libonig-dev libxml2-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath exif gd zip \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

RUN mkdir -p /etc/nginx/conf.d /var/cache/nginx /var/log/nginx /run/nginx \
 && mkdir -p /var/lib/nginx/body /var/lib/nginx/fastcgi /var/lib/nginx/proxy /var/lib/nginx/tmp \
 && chown -R www-data:www-data /etc/nginx /var/cache/nginx /var/log/nginx /run/nginx /var/lib/nginx

RUN sed -ri 's/^\s*(user|pid)\b/# &/g' /etc/nginx/nginx.conf
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf

WORKDIR /var/www

COPY --chown=www-data:www-data --from=vendor /app /var/www
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R g+rwX storage bootstrap/cache

ENV COMPOSER_HOME=/var/www/.composer
ENV COMPOSER_CACHE_DIR=/var/www/.composer/cache
RUN mkdir -p /var/www/.composer && chown -R www-data:www-data /var/www/.composer

COPY environment/nginx/default.conf.tpl /etc/nginx/templates/default.conf.template
COPY environment/docker/php.ini /usr/local/etc/php/conf.d/zz-prod.ini
COPY environment/docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-pool.conf
COPY environment/docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENV PORT=8080
EXPOSE 8080

USER www-data
CMD ["/entrypoint.sh"]
