
FROM php:8.2-apache
RUN docker-php-ext-install pdo_mysql mysqli
RUN a2enmod rewrite
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#g' /etc/apache2/sites-available/000-default.conf
RUN printf "<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/public-override.conf \
 && a2enconf public-override
