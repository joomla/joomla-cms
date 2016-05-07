#!/bin/bash

printf "\nUpdating composer\n"
composer update
printf "\nDownloading Joomla\n"
php cli/getjoomlacli.php
printf "\nPreparing Codeception\n"
php vendor/bin/codecept build
printf "\nRunning Installation test\n"
php vendor/bin/codecept run tests/acceptance/installation --steps --debug
printf "\nSetting Error Reporting to Development\n"
php vendor/bin/codecept run tests/acceptance/administrator/setDevelopmentErrorReportingCept.php --steps