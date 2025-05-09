FROM php:8.3-apache

# 必要なツールとPHP拡張機能のインストール
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apacheの設定
RUN a2enmod rewrite

# ワークディレクトリの設定
WORKDIR /var/www/html

# Apacheの公開ディレクトリを設定
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 開発環境用の設定
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/error-reporting.ini
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/error-reporting.ini
RUN echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/error-reporting.ini

# ディレクトリの準備と権限設定
RUN mkdir -p /var/www/html
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html 