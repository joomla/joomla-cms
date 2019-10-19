# System Testing for Joomla

### Abstract
Acceptance or UI tests for Joomla are present in the `acceptance` folder, there are two major categories of tests
at the moment
1) Installation Tests, inside the `acceptance/install` folder
2) Administrator Tests, inside the `acceptance/administrator` folder

### Installation
Here are the steps that are needed to setup UI tests execution on `localhost`

#### Linux OS
1) Checkout the Project in your document root folder, and follow the setup guide.
2) Navigate to `tests/Codeception` folder and edit configuration file `acceptance.suite.yml` file.
    1) Within the JoomlaBrowser config section change `url` point it to your localhost url
    2) update `database host` `database user` & `database password` as per your localhost installed DB,
    these values will be used by installation tests
    3) change `database name` make sure you have a database with this name created on your localhost DB,
    this will help you avoid errors with `JoomlaDb` helper as well.
    4) Within the `Helper/JoomlaDb` section, update the values for
    `host` `dbname` `user` `password` as per the previous section in the config
    5) Within the `Helper/Acceptance` section, update the values for
    `url` `cmsPath`, point them as per your localhost setup.
3) Run `./node_modules/.bin/selenium-standalone install` in project to install selenium-standalone server in localhost
4) Run `./node_modules/.bin/selenium-standalone start` and wait for the message `Selenium Started`
5) Run the following in project root: `libraries/vendor/bin/codecept run acceptance tests/Codeception/acceptance/install`
this will start Chrome in headless mode, to view the test execution in Chrome UI, remove `headless` from capabilities in configuration file  



# API testing for Joomla

### Abstract

These are the Joomla 4 API (webservices) tests.

### Installation

Run a `composer install` in the joomla root directory and adjust the REST url in 
`tests/Api/api.suite.yml` and copy the `codeception.yml` to the Joomla main directory. 

>Tests with authentication require a user `admin` with password `admin` as credentials for now. 

### Running

`vendor/bin/codecept run api`

You can also run the command with `--debug` to get some extended information.
