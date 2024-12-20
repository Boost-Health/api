FROM php:8.3-fpm

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
RUN export PATH=$PATH":/usr/bin"
RUN docker-php-ext-install pdo_mysql
RUN apt-get update && apt-get install -y \
    build-essential \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    zlib1g-dev \
    pkg-config \
    libicu-dev \
    libmcrypt-dev \
    curl \
    openssl \
    zip \
    unzip \
    git \
    && docker-php-ext-install -j$(nproc) pdo \
    && docker-php-ext-install -j$(nproc) pdo_pgsql \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && docker-php-ext-install  bcmath \
    && docker-php-ext-install  gd \
    && docker-php-ext-install  intl \
    && docker-php-ext-install  zip \
    && docker-php-ext-install opcache

RUN pecl install excimer && \
    echo "extension=excimer.so" > "$PHP_INI_DIR/conf.d/docker-php-ext-excimer.ini" && \
    php -m | grep -i excimer

RUN apt-get install nano -y

RUN apt-get install supervisor -y

RUN apt-get install -y nginx  && \
    rm -rf /var/lib/apt/lists/*

ENV TZ=Africa/Lagos
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /var/www/html

# Install dependencies
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-scripts --no-autoloader && rm -rf /root/.composer

# Copy codebase
COPY . ./

# Finish composer
RUN composer dump-autoload --optimize --no-scripts

RUN rm /etc/nginx/sites-enabled/default

COPY ./deploy/deploy.conf /etc/nginx/conf.d/default.conf

RUN mv /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf.backup
COPY ./deploy/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./deploy/supervisord.conf /etc/supervisord.conf

RUN usermod -a -G www-data root
RUN chgrp -R www-data storage

RUN chown -R www-data:www-data ./storage
RUN chmod -R 0777 ./storage
RUN chgrp -R www-data storage bootstrap/cache
RUN chmod -R ug+rwx storage bootstrap/cache

RUN chmod +x ./deploy/run

RUN ln -s /app/storage/logs /opt/logs


ENTRYPOINT ["./deploy/run"]

EXPOSE 80
