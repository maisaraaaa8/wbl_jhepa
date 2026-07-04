FROM php:8.3-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y git unzip zip curl libpq-dev libzip-dev && docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache

RUN chmod -R 775 storage bootstrap/cache

RUN if [ -f package.json ]; then curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs && npm install && npm run build; fi

EXPOSE 10000

CMD php artisan config:clear && php artisan serve --host=0.0.0.0 --port=10000