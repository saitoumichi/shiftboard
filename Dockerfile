# Render用のDockerfile
FROM php:8.2-apache

# 必要な拡張をインストール
RUN docker-php-ext-install mysqli pdo pdo_mysql

# mod_rewriteを有効化
RUN a2enmod rewrite

# DocumentRootをpublicディレクトリに設定
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# AllowOverride Allを設定（.htaccessを有効化）
RUN sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# アプリケーションファイルをコピー
COPY . /var/www/html

# 作業ディレクトリを設定
WORKDIR /var/www/html

# 権限設定
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# ポート80を公開
EXPOSE 80

# Apacheを起動
CMD ["apache2-foreground"]

