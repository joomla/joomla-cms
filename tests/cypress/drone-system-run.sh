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

cd /tests/www/$TEST_GROUP
php installation/joomla.php install --verbose --site-name="Joomla CMS test" --admin-email=admin@example.org --admin-username=ci-admin --admin-user="jane doe" --admin-password=joomla-17082005 --db-type=$DB_ENGINE --db-host=$DB_HOST --db-name=test_joomla --db-pass=joomla_ut --db-user=root --db-encryption=0 --db-prefix=$TEST_GROUP

# If you have found this line failing on OSX you need to brew install gnu-sed like we mentioned in the codeception readme!
# This replaces the site secret in configuration.php so we can guarantee a consistent API token for our super user.
sed -i "/\$secret/c\	public \$secret = 'tEstValue';" /tests/www/$TEST_SUITE/configuration.php

cd $JOOMLA_BASE
npx cypress run --browser=firefox --e2e --env db_type=$DB_ENGINE,db_host=$DB_HOST,db_password=joomla_ut,db_prefix="${TEST_GROUP}_" --config baseUrl=http://localhost/$TEST_GROUP,screenshotsFolder=$JOOMLA_BASE/tests/cypress/output/screenshots
