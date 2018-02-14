#!/bin/bash
# Script for preparing the unit tests in Joomla!

# Path to the Joomla! installation
BASE="$1"

# Abort travis execution if setup fails
set -e

# Disable xdebug.
phpenv config-rm xdebug.ini || echo "xdebug not available"

# Make sure all dev dependencies are installed, ignore platform requirements because Travis is missing the LDAP tooling on all new images
composer install --ignore-platform-reqs

# Setup databases for testing
mysql -u root -e 'create database joomla_ut;'
mysql -u root joomla_ut < "$BASE/tests/unit/schema/mysql.sql"
psql -c 'create database joomla_ut;' -U postgres
psql -d joomla_ut -a -f "$BASE/tests/unit/schema/postgresql.sql"

# Set up Apache
# - ./build/travis/php-apache.sh
# Enable additional PHP extensions

# Following the documentation at https://paragonie.com/book/pecl-libsodium/read/00-intro.md#installing-libsodium-source . We install in a custom
# directory because we're on sudo-less env and therefore cannot write into /usr/local/bin/ - so we do what google did with
# https://github.com/google/hat-backup/blob/master/travis-install-libsodium.sh and install into home and pass in custom
# pkg_config path - I'm not sure that we need to export LD_LIBRARY_PATH - but trying it from desperation as it was done in the
# google repo
#if [ $INSTALL_LIBSODIUM == "yes" ]; then
#  wget https://github.com/jedisct1/libsodium/releases/download/1.0.15/libsodium-1.0.15.tar.gz
#  tar xvfz libsodium-1.0.15.tar.gz
#  cd libsodium-1.0.15 && ./configure --prefix=$HOME/libsodium && make check && make install
#  cd ../
#  wget https://github.com/jedisct1/libsodium-php/archive/2.0.10.tar.gz
#  tar xvfz 2.0.10.tar.gz
#  cd libsodium-php-2.0.10
#  phpize
#  LD_LIBRARY_PATH=$HOME/libsodium/lib:$LD_LIBRARY_PATH PKG_CONFIG_PATH=$HOME/libsodium/lib/pkgconfig:$PKG_CONFIG_PATH ./configure --with-sodium
#  make install
#fi

if [[ $INSTALL_MEMCACHE == "yes" ]]; then phpenv config-add "$BASE/build/travis/phpenv/memcache.ini"; fi
if [[ $INSTALL_MEMCACHED == "yes" ]]; then phpenv config-add "$BASE/build/travis/phpenv/memcached.ini"; fi
if [[ $INSTALL_APC == "yes" ]]; then phpenv config-add "$BASE/build/travis/phpenv/apc-$TRAVIS_PHP_VERSION.ini"; fi
if [[ $INSTALL_APCU == "yes" && $TRAVIS_PHP_VERSION = 7.* ]]; then printf "\n" | pecl install apcu && phpenv config-add "$BASE/build/travis/phpenv/apcu-$TRAVIS_PHP_VERSION.ini"; fi
if [[ $INSTALL_REDIS == "yes" ]]; then phpenv config-add "$BASE/build/travis/phpenv/redis.ini"; fi
