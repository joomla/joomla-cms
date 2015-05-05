#!/bin/sh

VERSION=`phpenv version-name`

if [ "${VERSION}" = 'hhvm' ]
then
    PHPINI=/etc/hhvm/php.ini
    echo "extension_dir = /etc/hhvm" >> $PHPINI
    echo "hhvm.extensions[pgsql] = pgsql.so" >> $PHPINI
elif [ "${VERSION}" = '7.0' ]
then
    PHPINI=~/.phpenv/versions/$VERSION/etc/php.ini
    echo "extension = apc.so"  >> $PHPINI
    echo "extension = memcache.so"  >> $PHPINI
    echo "extension = memcached.so" >> $PHPINI
    echo "extension = redis.so"     >> $PHPINI
    phpenv config-add build/travis/phpenv/memcached.ini
    phpenv config-add build/travis/phpenv/apc-$VERSION.ini
    phpenv config-add build/travis/phpenv/redis.ini
else
    PHPINI=~/.phpenv/versions/$VERSION/etc/php.ini
    phpenv config-add build/travis/phpenv/memcached.ini
    phpenv config-add build/travis/phpenv/apc-$VERSION.ini
    phpenv config-add build/travis/phpenv/redis.ini
fi
