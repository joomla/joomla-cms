Testing Joomla CMS
==========

## System testing
This folder contains a System Tests suite based on Codeception Testing Framework. For more information see: https://docs.joomla.org/Testing_Joomla_Extensions_with_Codeception

### Getting Joomla
The first step to execute the System tests at Joomla-CMS a Joomla website. To do it automatically you can execute the following commands:

```
cd tests/codeception
composer install
# The following comand uses a Joomla Framework App that downloads the latests Joomla
php cli/getjoomlacli.php
```

note: to execute the previous commands you will need Composer in your system. See https://docs.joomla.org/Testing_Joomla_Extensions_with_Codeception#Testing_with_Codeception.


### Running the tests

Rename tests/acceptance.suite.dist.yml to tests/acceptance.suite.yml

Modify the configuration at tests/acceptance.suite.yml to fit your server details. Find the instructions in the same file: https://github.com/joomla/joomla-cms/tests/codeception/acceptance.suite.dist.yml#L3

Run Selenium server (is the software that drives your Firefox browser):

```
# Download Selenium Server
curl -O http://selenium-release.storage.googleapis.com/2.41/selenium-server-standalone-2.41.0.jar

# Go to the folder were you have downloaded the file and start the Selenium Server
java -Xms40m -Xmx256m -jar ./selenium-server-standalone-2.41.0.jar
```


Execute the tests:

```
php vendor/bin/codecept build
php vendor/bin/codecept run tests/acceptance/installation/ --steps 
php vendor/bin/codecept run tests/acceptance/administrator/ --steps 
```

You can also execute the tests using runsystemtests.sh file

