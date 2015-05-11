#!/bin/sh

VERSION=`phpenv version-name`

if [ "${VERSION}" = 'hhvm' ]
then
    PHPINI=/etc/hhvm/php.ini
    echo "extension_dir = /etc/hhvm" >> $PHPINI
    sudo apt-get update -qq
    sudo apt-get install -y zend-framework
    sudo apt-get install -y php-pear
    sudo apt-get install -y php5-mcrypt
    sudo apt-get install -y php5-imagick
    sudo apt-get install -y php5-mysql
    sudo apt-get install -y php5-memcached
    sudo apt-get install -y php5-xdebug
    #sudo apt-get install -y php5-apcu
    #sudo apt-get install -y php5-redis
    echo "extension = apcu.so" >> $PHPINI
    echo "apc.enable_cli=true" >> $PHPINI
    echo "extension = memcached.so" >> $PHPINI
    echo "extension = redis.so" >> $PHPINI
    # phpenv config-add build/travis/phpenv/apc-$VERSION.ini
    # echo "hhvm.extensions[pgsql] = pgsql.so" >> $PHPINI
elif [ "${VERSION}" = '7.0' ]
then
    PHPINI=~/.phpenv/versions/$VERSION/etc/php.ini
    sudo apt-get update -qq
    sudo apt-get install -y php-apc
    sudo apt-get install -y php5-curl
    sudo apt-get install -y php5-pgsql
    #sudo apt-get install -y php5-apcu
    #sudo apt-get install -y php5-redis
    echo "apc.enable_cli=true" >> $PHPINI
else
    PHPINI=~/.phpenv/versions/$VERSION/etc/php.ini
    phpenv config-add build/travis/phpenv/memcached.ini
    phpenv config-add build/travis/phpenv/apc-$VERSION.ini
    phpenv config-add build/travis/phpenv/redis.ini
fi
