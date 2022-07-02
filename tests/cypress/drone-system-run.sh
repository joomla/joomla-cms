#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
DB_ENGINE=$2

echo "[RUNNER] Prepare test environment"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files to test installation"
rsync -a --exclude-from=tests/Codeception/exclude.txt $JOOMLA_BASE/ /tests/www/$DB_ENGINE/
chown -R www-data /tests/www/$DB_ENGINE/

echo "[RUNNER] Start Apache"
apache2ctl -D FOREGROUND &

echo "[RUNNER] Run cypress"
cd /tests/www/$DB_ENGINE
export cypress_db_host=$DB_ENGINE
export cypress_db_password=joomla_ut

npm run cypress:install
npx cypress verify
npm run cypress:run-chrome --config baseUrl=http://localhost/$DB_ENGINE
