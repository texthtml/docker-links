FROM php:5.6

RUN docker-php-ext-install mbstring

ADD conf.d/ /usr/local/etc/php/conf.d/

ENTRYPOINT ["php", "/scripts/vendor/bin/behat"]

WORKDIR /scripts
