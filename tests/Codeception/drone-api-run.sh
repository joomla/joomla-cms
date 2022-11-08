#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
TEST_SUITE=$2
DB_ENGINE=$3
DB_HOST=$4
DB_PREFIX=$5


echo "[RUNNER] Prepare test environment"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files to test installation"
rsync -a --exclude-from=tests/Codeception/exclude.txt $JOOMLA_BASE/ /tests/www/$TEST_SUITE/
chown -R www-data /tests/www/$TEST_SUITE/

echo "[RUNNER] Start Apache & Chrome"
apache2ctl -D FOREGROUND &
google-chrome --version

echo "[RUNNER] Start Selenium"
selenium-standalone start > selenium.api.$TEST_SUITE.log 2>&1 &
echo -n "Waiting until Selenium is ready"
until $(curl --output /dev/null --silent --head --fail http://localhost:4444/wd/hub/status); do
    printf '.'
    sleep 2
done
echo .

echo "[RUNNER] Install Joomla"
cd /tests/www/$TEST_SUITE
php installation/joomla.php install --verbose --site-name="Joomla CMS test" --admin-email=admin@example.org --admin-username=ci-admin --admin-user="jane doe" --admin-password=joomla-17082005 --db-type=$DB_ENGINE --db-host=$DB_HOST --db-name=test_joomla --db-pass=joomla_ut --db-user=root --db-encryption=0 --db-prefix=$DB_PREFIX

# If you have found this line failing on OSX you need to brew install gnu-sed like we mentioned in the codeception readme!
# This replaces the site secret in configuration.php so we can guarantee a consistent API token for our super user.
sed -i "/\$secret/c\	public \$secret = 'tEstValue';" /tests/www/$TEST_SUITE/configuration.php

echo "[RUNNER] Run Codeception"
# Executing API tests
php libraries/vendor/bin/codecept run api --fail-fast --steps --debug --env $TEST_SUITE
