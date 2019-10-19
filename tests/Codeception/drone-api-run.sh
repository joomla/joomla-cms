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

# Executing API tests
libraries/vendor/bin/codecept run api --fail-fast --steps --debug
