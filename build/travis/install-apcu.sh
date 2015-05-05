#!/bin/bash

# this is helpful to compile the extension
sudo apt-get install autoconf

# install this version
APCU=4.0.7

# compile manually, because `pecl install apcu-beta` keeps asking questions
wget http://pecl.php.net/get/apcu-$APCU.tgz
tar zxvf apcu-$APCU.tgz
cd "apcu-${APCU}"
phpize && ./configure && make install && echo "Installed ext/apcu-${APCU}"
