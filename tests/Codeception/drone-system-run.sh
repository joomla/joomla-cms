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


echo "[RUNNER] Prepare Selenium"
mkdir -p tests/Codeception/_output
if [[ -f "/usr/lib/node_modules/selenium-standalone/lib/default-config.js" ]]; then
	flock tests/Codeception/_output/selenium.config.js cp /usr/lib/node_modules/selenium-standalone/lib/default-config.js tests/Codeception/_output/selenium.config.js
fi

echo "[RUNNER] Start Selenium"
selenium-standalone start > tests/Codeception/_output/selenium.$DB_ENGINE.log 2>&1 &
echo -n "Waiting until Selenium is ready"
until $(curl --output /dev/null --silent --head --fail http://localhost:4444/wd/hub/status); do
    printf '.'
    sleep 2
done
echo .

echo "[RUNNER] Run Codeception"
cd /tests/www/$DB_ENGINE
php libraries/vendor/bin/codecept run acceptance --fail-fast --steps --debug --env $DB_ENGINE
