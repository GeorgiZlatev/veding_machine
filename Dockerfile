FROM composer:2 AS dependencies

WORKDIR /app

COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.2-cli

WORKDIR /app

COPY --from=dependencies /app/vendor ./vendor
COPY . .

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "."]
