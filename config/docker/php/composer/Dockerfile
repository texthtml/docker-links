FROM php:5.6

RUN apt-get update
RUN apt-get install -y zlib1g-dev
RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

ENTRYPOINT ["/usr/local/bin/composer"]

WORKDIR /scripts
