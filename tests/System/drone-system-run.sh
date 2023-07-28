#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
TEST_GROUP=$2
DB_ENGINE=$3
DB_HOST=$4

DB_USERNAME=root
DB_PASSWORD=joomla_ut
DB_NAME=test_joomla

#BASE_URL="http://localhost:8181/${TEST_GROUP}"

BASE_URL="http://localhost:8181"

echo "[RUNNER] Prepare test environment"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files to test installation"
rsync -a --exclude-from=tests/System/exclude.txt $JOOMLA_BASE/ /tests/www/$TEST_GROUP/
chown -R www-data /tests/www/$TEST_GROUP/

# Required for media manager tests
chmod -R 777 /tests/www/$TEST_GROUP/images

echo "[RUNNER] Start Apache"
#apache2ctl -D FOREGROUND &
JOOMLA_INSTALLATION_DISABLE_LOCALHOST_CHECK="1" php -S 127.0.0.1:8181 -t /tests/www/$TEST_GROUP/ &

echo "[RUNNER] Run cypress tests"
chmod +rwx /root

npx cypress run --headed --browser=firefox --e2e --env cmsPath="/tests/www/${TEST_GROUP}",db_type="${DB_ENGINE}",db_host="${DB_HOST}",db_name="${DB_NAME}",db_user="${DB_USERNAME}",db_password="${DB_PASSWORD}",db_prefix="${TEST_GROUP}_" --config baseUrl="${BASE_URL}",screenshotsFolder=$JOOMLA_BASE/tests/System/output/screenshots
