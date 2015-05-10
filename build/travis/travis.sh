#!/bin/sh

VERSION=`phpenv version-name`

if [ "${VERSION}" = 'hhvm' ]
then
    PHPINI=/etc/hhvm/php.ini
    echo "memory_limit = -1"  >> $PHPINI
    sudo apt-get update -qq
    sudo apt-get install -y php5-apcu
    sudo apt-get install -y php5-memcached
    sudo apt-get install -y redis-server
    sudo apt-get install -y php5-redis
    # phpenv config-add build/travis/phpenv/apc-$VERSION.ini
    # echo "extension_dir = /etc/hhvm" >> $PHPINI
    # echo "hhvm.extensions[pgsql] = pgsql.so" >> $PHPINI
elif [ "${VERSION}" = '7.0' ]
then
    PHPINI=~/.phpenv/versions/$VERSION/etc/php.ini
    sudo apt-get install -y php5-apcu
    sudo apt-get install -y php5-memcached
    sudo apt-get install -y php5-redis
    phpenv config-add build/travis/phpenv/memcached.ini
    phpenv config-add build/travis/phpenv/apc-$VERSION.ini
    phpenv config-add build/travis/phpenv/redis.ini
else
    PHPINI=~/.phpenv/versions/$VERSION/etc/php.ini
    phpenv config-add build/travis/phpenv/memcached.ini
    phpenv config-add build/travis/phpenv/apc-$VERSION.ini
    phpenv config-add build/travis/phpenv/redis.ini
fi
