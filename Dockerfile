FROM php:8.2-fpm

# System dependecies GD and SQLite
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Create folders
# /app and /public_html inside /var/www to simulate share hosting in same folder level
RUN mkdir -p /var/www/app && \
    chown -R www-data:www-data /var/www/app && \
    chmod -R 755 /var/www/app
    
RUN mkdir -p /var/www/html && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

WORKDIR /var/www/app

COPY ./app /var/www/app
COPY ./public_html/ /var/www/html/

EXPOSE 9000

CMD ["php-fpm"]
