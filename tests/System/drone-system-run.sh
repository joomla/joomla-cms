#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
TEST_GROUP=$2
DB_ENGINE=$3
DB_HOST=$4

echo "[RUNNER] Prepare test environment"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files to test installation"
rsync -a --exclude-from=tests/System/exclude.txt $JOOMLA_BASE/ /tests/www/$TEST_GROUP/
chown -R www-data /tests/www/$TEST_GROUP/

# Required for media manager tests
chmod -R 777 /tests/www/$TEST_GROUP/images

echo "[RUNNER] Start Apache"
a2enmod rewrite
apache2ctl -D FOREGROUND &

echo "[RUNNER] Run cypress tests"
chmod +rwx /root

npx cypress run --browser=firefox --e2e --env cmsPath=/tests/www/$TEST_GROUP,db_type=$DB_ENGINE,db_host=$DB_HOST,db_password=joomla_ut,db_prefix="${TEST_GROUP}_" --config baseUrl=http://localhost/$TEST_GROUP,screenshotsFolder=$JOOMLA_BASE/tests/System/output/screenshots
