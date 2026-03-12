FROM richarvey/nginx-php-fpm:3.1.6

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY .env.railway /var/www/html/.env

RUN php artisan config:clear && \
    php artisan cache:clear

EXPOSE 80