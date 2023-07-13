#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
TYPE=$2

echo "[RUNNER] Prepare test environment"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files to test installation"
rsync -a $JOOMLA_BASE/ /tests/www/$TYPE/
chown -R www-data /tests/www/$TYPE/

echo "[RUNNER] cypress"
cd /tests/www/$TYPE
npm run cyress:run
