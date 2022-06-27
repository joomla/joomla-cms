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

echo "[RUNNER] Start Selenium"
selenium-standalone start > selenium.api.$DB_ENGINE.log 2>&1 &
echo -n "Waiting until Selenium is ready"
until $(curl --output /dev/null --silent --head --fail http://localhost:4444/wd/hub/status); do
    printf '.'
    sleep 2
done
echo .

echo "[RUNNER] Run Codeception"
cd /tests/www/$DB_ENGINE
php libraries/vendor/bin/codecept run --fail-fast --steps --debug --env $DB_ENGINE tests/Codeception/acceptance/01-install/

# If you have found this line failing on OSX you need to brew install gnu-sed like we mentioned in the codeception readme!
# This replaces the site secret in configuration.php so we can guarantee a consistent API token for our super user.
sed -i "/\$secret/c\	public \$secret = 'tEstValue';" /tests/www/$DB_ENGINE/configuration.php

# Executing API tests
php libraries/vendor/bin/codecept run api --fail-fast --steps --debug --env $DB_ENGINE
