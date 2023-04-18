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
rsync -a --exclude-from=tests/cypress/exclude.txt $JOOMLA_BASE/ /tests/www/$TEST_GROUP/
chown -R www-data /tests/www/$TEST_GROUP/

echo "[RUNNER] Start Apache"
apache2ctl -D FOREGROUND &

echo "[RUNNER] Run cypress"
chmod +rwx /root
# cd /tests/www/$TEST_GROUP

#export CYPRESS_CACHE_FOLDER=/tests/www/$DB_ENGINE/.cache

#npx cypress install
#npx cypress verify
npx cypress run --browser=firefox --e2e --env db_type=$DB_ENGINE,db_host=$DB_HOST,db_password=joomla_ut,db_prefix="${TEST_GROUP}_" --config baseUrl=http://localhost/$TEST_GROUP,screenshotsFolder=$JOOMLA_BASE/tests/cypress/output/screenshots

