FROM richarvey/nginx-php-fpm:3.1.6

COPY . /var/www/html

ENV WEBROOT /var/www/html/public
ENV APP_TYPE laravel
ENV SKIP_COMPOSER 0
ENV PHP_ERRORS_STDERR 1

RUN composer install --no-dev --optimize-autoloader

EXPOSE 80