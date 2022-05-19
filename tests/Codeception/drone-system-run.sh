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

echo "[RUNNER] Start Apache & Chrome"
apache2ctl -D FOREGROUND &
google-chrome --version

echo "[RUNNER] Run Codeception"
php libraries/vendor/bin/codecept run --fail-fast --steps --debug --env $DB_ENGINE tests/Codeception/acceptance/
