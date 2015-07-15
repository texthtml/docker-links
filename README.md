# Docker Links Environment Parser

[![Build Status](https://img.shields.io/travis/texthtml/docker-links.svg)](https://travis-ci.org/texthtml/docker-links)
[![Code Status](https://img.shields.io/scrutinizer/g/texthtml/docker-links.svg)](https://scrutinizer-ci.com/g/texthtml/docker-links/build-status/master)
[![Latest Version](https://img.shields.io/packagist/v/texthtml/docker-links.svg)](https://packagist.org/packages/texthtml/docker-links)
[![License](https://img.shields.io/packagist/l/texthtml/docker-links.svg)](https://packagist.org/packages/texthtml/docker-links)

[Docker](http://www.docker.io/) has a feature where you can [link containers together by name](http://docs.docker.io/en/latest/use/working_with_links_names/). For example, you start a redis-server in a docker container and expose the default redis port 6379:

    $ docker run -p 6379 -d -name redis vagrant/redis-server

You then start another containiner running a php-fpm web service that needs to access this redis server:

    $ docker run --link redis:db -d php:fpm
  
Docker will internally hook up these the two containers and pass host and port information to the php-fpm web service via environment variables:

    DB_NAME=/romantic_lumiere/db
    DB_PORT=tcp://172.17.0.5:6379
    DB_PORT_6379_TCP=tcp://172.17.0.5:6379
    DB_PORT_6379_TCP_ADDR=172.17.0.5
    DB_PORT_6379_TCP_PORT=6379
    DB_PORT_6379_TCP_PROTO=tcp

This library provides a helper `parseLinks` that will parse these environment variables into easily navigable PHP objects.

### Install

Install `docker-links` via [composer](https://getcomposer.org/)

    $ composer require texthtml/docker-links

### Example Usage

Consider a container that accesses three external services on two other containers. The first container exposes redis on port 6379 and postgres on 6500. The second container exposes redis on port 6379.

    DB_NAME=/romantic_lumiere/db
    DB_PORT=tcp://172.17.0.5:6379
    DB_PORT_6379_TCP=tcp://172.17.0.5:6379
    DB_PORT_6379_TCP_ADDR=172.17.0.5
    DB_PORT_6379_TCP_PORT=6379
    DB_PORT_6379_TCP_PROTO=tcp
    DB_PORT_6500_TCP=tcp://172.17.0.5:6500
    DB_PORT_6500_TCP_ADDR=172.17.0.5
    DB_PORT_6500_TCP_PORT=6500
    DB_PORT_6500_TCP_PROTO=tcp
    DB_REDIS_NAME=/romantic_lumiere/db_redis
    DB_REDIS_PORT=tcp://172.17.0.2:6379
    DB_REDIS_PORT_6379_TCP=tcp://172.17.0.2:6379
    DB_REDIS_PORT_6379_TCP_ADDR=172.17.0.2
    DB_REDIS_PORT_6379_TCP_PORT=6379
    DB_REDIS_PORT_6379_TCP_PROTO=tcp

Parse with `docker-links`:

```php
$links = \TH\Docker\Links::buildFrom($_ENV);

// $links can be used as an array
echo count($links), PHP_EOL; // 2
foreach ($links as $name => $link) {
    echo $name, PHP_EOL; // /romantic_lumiere/db, /romantic_lumiere/db
}

// each link is an instanceof [Link](test)
$link = $links['/romantic_lumiere/db'];

echo $link->mainPort()->address(), PHP_EOL; // 172.17.0.5
echo $link->mainPort()->protocol(), PHP_EOL; // TCP
echo $link->mainPort()->number(), PHP_EOL; // 6379

echo $link->env()['USERNAME'], PHP_EOL; // username
echo $link->env('PASSWORD'), PHP_EOL; // password
echo $link->env('SOMETHING_ELSE'), PHP_EOL; // NULL
echo $link->env('SOMETHING', 'default value'), PHP_EOL; // default value
```
