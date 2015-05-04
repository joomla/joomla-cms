#!/bin/sh

VERSION=`phpenv version-name`

if [ "${VERSION}" = 'hhvm' ]
then
    PHPINI=/etc/hhvm/php.ini
else
    PHPINI=~/.phpenv/versions/$VERSION/etc/php.ini
    phpenv config-add build/travis/phpenv/memcached.ini
    phpenv config-add build/travis/phpenv/apc-$VERSION.ini
    phpenv config-add build/travis/phpenv/redis.ini
#    echo "extension = memcache.so"  >> $PHPINI
#    echo "extension = memcached.so" >> $PHPINI
#    echo "extension = redis.so"     >> $PHPINI
fi
