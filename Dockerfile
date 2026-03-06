FROM richarvey/nginx-php-fpm:3.1.6

COPY . /var/www/html

ENV WEBROOT /var/www/html/public
ENV APP_TYPE laravel
ENV SKIP_COMPOSER 0
ENV PHP_ERRORS_STDERR 1

RUN composer install --no-dev --optimize-autoloader

EXPOSE 80

# 1. Permisos de carpetas
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 2. Comando Maestro: Incluye el SEEDER con --force
CMD sh -c "sed -i 's|root /var/www/html|root /var/www/html/public|g' /etc/nginx/sites-available/default.conf && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    php artisan db:seed --force && \
    supervisord -n"