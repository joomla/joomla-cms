#!/bin/bash
# Script for preparing the unit tests in Joomla!

# Path to the Joomla! installation
BASE="$1"

# Abort travis execution if setup fails
set -e

# Disable xdebug on php 7.0.* and lower.
if [[ ( $TRAVIS_PHP_VERSION = 5.* ) || ( $TRAVIS_PHP_VERSION = 7.0 ) ]]; then phpenv config-rm xdebug.ini; fi

# Disable xdebug in hhvm.
if [[ $TRAVIS_PHP_VERSION = hhv* ]]; then echo 'xdebug.enable = 0' >> /etc/hhvm/php.ini; fi

# Disable JIT compilation in hhvm.
if [[ $TRAVIS_PHP_VERSION = hhv* ]]; then echo 'hhvm.jit = 0' >> /etc/hhvm/php.ini; fi

# Make sure all dev dependencies are installed
composer install

# Setup databases for testing
mysql -u root -e 'create database joomla_ut;'
mysql -u root joomla_ut < "$BASE/tests/unit/schema/mysql.sql"
psql -c 'create database joomla_ut;' -U postgres
psql -d joomla_ut -a -f "$BASE/tests/unit/schema/postgresql.sql"

# Set up Apache
# - ./build/travis/php-apache.sh
# Enable additional PHP extensions

if [[ $INSTALL_MEMCACHE == "yes" ]]; then phpenv config-add "$BASE/build/travis/phpenv/memcache.ini"; fi
if [[ $INSTALL_MEMCACHED == "yes" ]]; then phpenv config-add "$BASE/build/travis/phpenv/memcached.ini"; fi
if [[ $INSTALL_APC == "yes" ]]; then phpenv config-add "$BASE/build/travis/phpenv/apc-$TRAVIS_PHP_VERSION.ini"; fi
if [[ $INSTALL_APCU == "yes" && $TRAVIS_PHP_VERSION = 5.* ]]; then printf "\n" | pecl install apcu-4.0.10 && phpenv config-add "$BASE/build/travis/phpenv/apcu-$TRAVIS_PHP_VERSION.ini"; fi
if [[ $INSTALL_APCU == "yes" && $TRAVIS_PHP_VERSION = 7.* ]]; then printf "\n" | pecl install apcu-beta && phpenv config-add "$BASE/build/travis/phpenv/apcu-$TRAVIS_PHP_VERSION.ini"; fi
if [[ $INSTALL_APCU_BC_BETA == "yes" ]]; then printf "\n" | pecl install apcu_bc-beta; fi
if [[ $INSTALL_REDIS == "yes" && $TRAVIS_PHP_VERSION != hhvm ]]; then phpenv config-add "$BASE/build/travis/phpenv/redis.ini"; fi
if [[ $INSTALL_REDIS == "yes" && $TRAVIS_PHP_VERSION = hhvm ]]; then cat "$BASE/build/travis/phpenv/redis.ini" >> /etc/hhvm/php.ini; fi
