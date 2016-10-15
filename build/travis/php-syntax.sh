#!/bin/bash
# Script for php/hhvm syntax check in Joomla!

RUN_COMMAND="php -d display_errors=stderr -n -l"

if [[ $TRAVIS_PHP_VERSION = hhvm ]]; then RUN_COMMAND="hhvm -d display_errors=stderr -l"; fi

# We exclude any 3rd Party folders / files here
find . \( -path ./libraries/vendor -o -path ./libraries/fof -o -path ./libraries/idna_convert -o -path ./libraries/php-encryption -o -path ./libraries/phputf8 -o -path ./libraries/phpass -o -path ./tests -o -wholename './administrator/components/com_joomlaupdate/restore.php' \) -prune -o -type f -name "*.php" -print | xargs -n1 -P8 $RUN_COMMAND 1>/dev/null