#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
TEST_SUITE=$2
DB_ENGINE=$3
DB_HOST=$4


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

echo "[RUNNER] Run Codeception"
cd /tests/www/$TEST_SUITE
php installation\joomla.php install --verbose --site_name="Joomla CMS test" --admin_email=admin@example.org --admin_username=ci-admin --admin_user="jane doe" --admin_password=joomla-17082005 --db_type=$DB_ENGINE --db_host=$DB_HOST --db_name=test_joomla --db_pass=joomla_ut --db_user=root --db_encryption=0

# If you have found this line failing on OSX you need to brew install gnu-sed like we mentioned in the codeception readme!
# This replaces the site secret in configuration.php so we can guarantee a consistent API token for our super user.
sed -i "/\$secret/c\	public \$secret = 'tEstValue';" /tests/www/$TEST_SUITE/configuration.php

# Executing API tests
php libraries/vendor/bin/codecept run api --fail-fast --steps --debug --env $TEST_SUITE
