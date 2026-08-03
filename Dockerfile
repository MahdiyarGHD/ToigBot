FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-install=source --no-interaction --no-progress

FROM php:8.3-apache
WORKDIR /var/www/html

COPY --from=vendor /app/vendor /var/www/html/vendor
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html/data

EXPOSE 80

CMD ["apache2-foreground"]
