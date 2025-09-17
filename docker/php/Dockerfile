# ./docker/php/Dockerfile
FROM php:8.2-apache

# 必要拡張
RUN docker-php-ext-install mysqli pdo pdo_mysql

# DocumentRoot を public/ に変更
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
&& sed -ri 's!DocumentRoot.*!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf


# .htaccess を効かせる
RUN a2enmod rewrite
# 既定の vhost に AllowOverride を許可（public/.htaccess を使うため）
RUN sed -ri 's!<Directory /var/www/>!<Directory /var/www/html/public/>!g' /etc/apache2/apache2.conf \
&& sed -ri '/<Directory \/var\/www\/html\/public\/>/,/<\/Directory>/{s/AllowOverride None/AllowOverride All/}' /etc/apache2/apache2.conf

WORKDIR /var/www/html