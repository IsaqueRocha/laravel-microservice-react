FROM php:7.4-fpm-alpine

RUN apk add --no-cache openssl bash mysql-client nodejs npm git freetype-dev libjpeg-turbo-dev libpng-dev
RUN docker-php-ext-install pdo pdo_mysql bcmath
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-install -j$(nproc) gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV DOCKERIZE_VERSION v0.6.1
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz


WORKDIR /var/www

RUN rm -rf /var/www/html
RUN ln -s public html

ADD .env /templates/.env
# ADD .env.testing /templates/.env.testing
ADD entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

RUN usermod -u 1000 www-data

EXPOSE 9000

# ENTRYPOINT [ "php-fpm"]
