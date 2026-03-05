FROM richarvey/nginx-php-fpm:3.1.6

COPY . /var/www/html

ENV WEBROOT /var/www/html/public
ENV APP_TYPE laravel
ENV SKIP_COMPOSER 0
ENV PHP_ERRORS_STDERR 1

RUN composer install --no-dev --optimize-autoloader

EXPOSE 80

# 1. Dar permisos a las carpetas de Laravel (vital para que no dé error 500)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 2. Comando para ejecutar migraciones y encender el servidor
CMD php artisan migrate --force && /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf