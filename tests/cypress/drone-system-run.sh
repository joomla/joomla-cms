#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
TEST_GROUP=$2
DB_ENGINE=$3

echo "[RUNNER] Prepare test environment"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files to test installation"
rsync -a $JOOMLA_BASE/ /tests/www/$TEST_GROUP/
chown -R www-data /tests/www/$TEST_GROUP/

echo "[RUNNER] Start Apache"
apache2ctl -D FOREGROUND &

echo "[RUNNER] Run cypress"
#cd /tests/www/$DB_ENGINE
chmod +rwx /root

#export CYPRESS_CACHE_FOLDER=/tests/www/$DB_ENGINE/.cache
export cypress_db_host=$DB_ENGINE
export cypress_db_password=joomla_ut

npm run cypress:install
npx cypress verify
npx cypress run --browser=firefox --e2e --config baseUrl=http://localhost/$TEST_GROUP

