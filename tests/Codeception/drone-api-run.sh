#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
HEADER=$(cat <<'EOF'
......._......................._........
......| |.....................| |.......
......| |.___...___.._.__.___.| |.__._..
.._...| |/ _ \./ _ \|  _   _ \| |/ _  |.
.| |__| | (_) | (_) | |.| |.| | | (_) |.
..\____/.\___/.\___/|_|.|_|.|_|_|\__,_|.
........................................
.............__......._____..___........
............/  \.....|  __ \.| |........
.........../ /\ \....| |__) || |........
........../ /__\ \...|  ___/.| |........
........./  ____\ \..| |.....| |........
......../_/......\ \.|_|...._|_|_.......
........................................
...._______........_..._................
...|__   __|......| |.(_)...............
......| |.___..___| |_._._.__...__._....
......| |/ _ \/ __| __| |  _ \./ _  |...
......| |  __/\__ \ |_| | |.| | (_) |...
......|_|\___||___/\__|_|_|.|_|\__  |...
................................__/ |...
...............................|____|...
#
EOF
)

tput setaf 2 -T xterm
echo "-------------------------------"
echo -e "${HEADER}"
echo "-------------------------------"
tput sgr0 -T xterm

echo "[RUNNER] Prepare test environment"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files to test installation"
rsync -a --exclude-from=tests/Codeception/exclude.txt $JOOMLA_BASE/ /tests/www/test-install/
chown -R www-data /tests/www/test-install/

echo "[RUNNER] Start Apache & Chrome"
apache2ctl -D FOREGROUND &
google-chrome --version

echo "[RUNNER] Start Selenium"
./node_modules/.bin/selenium-standalone install --drivers.chrome.version=77.0.3865.40 --drivers.chrome.baseURL=https://chromedriver.storage.googleapis.com
./node_modules/.bin/selenium-standalone start --drivers.chrome.version=77.0.3865.40 --drivers.chrome.baseURL=https://chromedriver.storage.googleapis.com >> selenium.log 2>&1 &
sleep 5

echo "[RUNNER] Run Codeception"
php libraries/vendor/bin/codecept build
php libraries/vendor/bin/codecept run --fail-fast --steps --debug --env mysql tests/Codeception/acceptance/01-install/

# TODO: This sed command doesn't work on OSX - can we rewrite it into something that does work? Good enough for the
#       linux docker container for now.
sed -i "/\$secret/c\	public \$secret = 'tEstValue';" /tests/www/test-install/configuration.php

# These credentials are duplicated with the ones in acceptance.suite.dist.yml
export MYSQL_ROOT="root"
export MYSQL_PASS="joomla_ut"
export TABLE_PREFIX="jos"

mysql -u$MYSQL_ROOT -p$MYSQL_PASS -e "SELECT \`id\` FROM ${TABLE_PREFIX}_users"

# Now we need to update the user profile table in order to set data:
# 1. #__user_profiles User ID for our account: profile_key: joomlatoken.enabled value: 1
# 2. #__user_profiles User ID for our account: profile_key: joomlatoken.token value: dOi2m1NRrnBHlhaWK/WWxh3B5tqq1INbdf4DhUmYTI4=

# Executing API tests
php libraries/vendor/bin/codecept run api --fail-fast --steps --debug
