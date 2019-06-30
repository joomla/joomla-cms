#!/bin/bash
# Script for preparing the unit tests in Joomla!

# Path to the Joomla! installation
BASE="/opt/src"

until mysqladmin ping -h mysql --silent; do
  sleep 1
done

>&2 echo "Mysql alive!"

until psql -h "postgres" -U "postgres"  --quiet -o /dev/null -c '\l'; do
  sleep 1
done

>&2 echo "Postgres alive!"

# Setup databases for testing
mysql -u root joomla_ut -h mysql -pjoomla_ut < "$BASE/tests/unit/schema/mysql.sql"
psql -c 'create database joomla_ut;'  -U postgres -h "postgres" > /dev/null
psql -U "postgres" -h "postgres" -d joomla_ut -a -f "$BASE/tests/unit/schema/postgresql.sql" > /dev/null

echo "Testing $PHPVERSION"

phpunit -c $BASE/jenkins-phpunit.xml