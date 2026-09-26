#!/usr/bin/env bash

# This script is intended to be used as a docker entrypoint file within a joomlaprojects/docker-images:cypressX image
# Check out the docker-compose.yml file how to prepare docker to run it or execute `docker compose up system-tests`.

set -e
JOOMLA_BASE=$1
TEST_GROUP=$2
DB_ENGINE=$3
DB_HOST=$4
BROWSER=${5:-firefox}

echo "[RUNNER] Prepare test environment for $BROWSER"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files from $JOOMLA_BASE to test installation /tests/www/$TEST_GROUP/"
rsync -a --exclude-from=tests/System/exclude.txt $JOOMLA_BASE/ /tests/www/$TEST_GROUP/
chown -R www-data /tests/www/$TEST_GROUP/

# Required for media manager tests
chmod -R 777 /tests/www/$TEST_GROUP/images

# Disable opcache for configuration.php, otherwise there are issues when the config is changed in a test
echo "/tests/www/$TEST_GROUP/configuration.php" > /tmp/blacklist.ini
echo "opcache.blacklist_filename=/tmp/blacklist.ini" >> /etc/php/*/apache2/conf.d/10-opcache.ini

echo "[RUNNER] Start Apache"
a2enmod rewrite
apache2ctl -D FOREGROUND &

echo "[RUNNER] Run cypress tests"
chmod +rwx /root

# Copy the cypress config if it doesn't exist
if [ ! -f cypress.config.mjs ]; then
    cp cypress.config.dist.mjs cypress.config.mjs
fi

# Do an install if the cache folder is empty
if [ -z "$( ls -A '/root/.cache/Cypress' )" ]; then
  npx cypress install
  npx cypress verify
fi

npx cypress run --browser=$BROWSER --e2e --env cmsPath=/tests/www/$TEST_GROUP,db_type=$DB_ENGINE,db_host=$DB_HOST,db_password=joomla_ut,db_prefix="${TEST_GROUP}_",logFile=/var/log/apache2/error.log --config baseUrl=https://localhost/$TEST_GROUP,screenshotsFolder=$JOOMLA_BASE/tests/System/output/screenshots
