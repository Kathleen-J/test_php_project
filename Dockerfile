FROM debian:11
ENV DEBIAN_FRONTEND noninteractive

WORKDIR /home/www-data/service

RUN mkdir -p /home/www-data/service && chown www-data:www-data /home/www-data/service

COPY composer.json composer.lock ./
COPY .env /home/www-data/service/.env

RUN apt-get update && apt-get install apt-transport-https lsb-release ca-certificates wget -y
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'

RUN apt-get update && \
    apt-get install -y \
    nginx \
    htop \
    nano \
    apt-utils \
    curl \
    git \
    unzip \
    php8.1 \
    php8.1-fpm \
    php8.1-cli \
    php8.1-dom \
    php8.1-curl \
    php8.1-pdo \
    php8.1-xml \
    php8.1-mbstring \
    php8.1-pgsql \
    php8.1-intl \
    php8.1-mcrypt \
    php8.1-memcache \
    php8.1-memcached \
    php8.1-redis

RUN rm -f /etc/nginx/nginx.conf && \
    rm -f /etc/php/8.1/fpm/pool.d/www.conf \
    rm -f /etc/nginx/sites-enabled/service

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    chmod +x /usr/local/bin/composer

ADD nginx/service.conf /etc/nginx/sites-enabled/default
ADD nginx/nginx.conf /etc/nginx/nginx.conf
ADD nginx/www.conf /etc/php/8.1/fpm/pool.d/www.conf

RUN composer install --no-cache

COPY . .

RUN chown -R www-data:www-data /home/www-data/service

EXPOSE $SERVICE_PORTS

CMD /etc/init.d/php8.1-fpm start && nginx -g "daemon off;"