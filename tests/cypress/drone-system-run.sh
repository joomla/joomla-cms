#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
DB_ENGINE=$2

echo "[RUNNER] Prepare test environment"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files to test installation"
rsync -a $JOOMLA_BASE/ /tests/www/$DB_ENGINE/
chown -R www-data /tests/www/$DB_ENGINE/

echo "[RUNNER] Start Apache"
apache2ctl -D FOREGROUND &

echo "[RUNNER] Run cypress"
cd /tests/www/$DB_ENGINE
chmod +rwx /root
mkdir /root/.cache
chmod +rwx /root/.cache

export CYPRESS_CACHE_FOLDER=/tests/www/$DB_ENGINE/.cache
export cypress_db_host=$DB_ENGINE
export cypress_db_password=joomla_ut

npm run cypress:install
npx cypress verify
npx cypress run --browser=chrome --e2e --config baseUrl=http://localhost/$DB_ENGINE

