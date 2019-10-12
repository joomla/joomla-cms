#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
DB_ENGINE=$2
HEADER=$(cat <<'EOF'
......._......................._........
......| |.....................| |.......
......| |.___...___.._.__.___.| |.__._..
.._...| |/ _ \./ _ \|  _   _ \| |/ _  |.
.| |__| | (_) | (_) | |.| |.| | | (_) |.
..\____/.\___/.\___/|_|.|_|.|_|_|\__,_|.
........................................
...._____..........._...................
.../ ____|.........| |..................
..| (___. _..._.___| |_.___._.__.____...
...\___ \| |.| / __| __/ _ \  _   _  |..
...____) | |_| \__ \ ||  __/ |.| |.| |..
..|_____/ \__, |___/\__\___|_|.|_|.|_|..
...........__/ |........................
..........|___/.........................
........................................
...._______........_..._................
...|__   __|......| |.(_)...............
......| |.___..___| |_._._.__...__._....
......| |/ _ \/ __| __| |  _ \./ _  |...
......| |  __/\__ \ |_| | |.| | (_) |...
......|_|\___||___/\__|_|_|.|_|\__  |...
................................__/ |...
...............................|____|...
EOF
)


tput setaf 2 -T xterm
echo "-------------------------------"
echo "${HEADER}"
echo "-------------------------------"
tput sgr0 -T xterm

echo "[RUNNER] Prepare test environment"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files to test installation"
rsync -ar --exclude-from=tests/Codeception/exclude.txt $JOOMLA_BASE/ /tests/www/test-install/

echo "[RUNNER] Start Apache & Chrome"
apache2ctl -D FOREGROUND &
google-chrome --version

echo "[RUNNER] Make chromedriver executable"
chmod 755 libraries/vendor/joomla-projects/selenium-server-standalone/bin/webdrivers/chrome/linux/chromedriver

echo "[RUNNER] Start Selenium"
export PATH="$PATH:$JOOMLA_BASE/libraries/vendor/joomla-projects/selenium-server-standalone/bin/webdrivers/chrome/linux"
echo $PATH
java -jar libraries/vendor/joomla-projects/selenium-server-standalone/bin/selenium-server-standalone.jar >> selenium.log 2>&1 &
sleep 3

echo "[RUNNER] Run Codeception"
php libraries/vendor/bin/codecept build
php libraries/vendor/bin/codecept run --fail-fast --steps --debug --env "$DB_ENGINE" tests/Codeception/acceptance/
