# System Testing for Joomla

### Abstract
Acceptance or UI tests for Joomla are present in the `acceptance` folder, there are two major categories of tests
at the moment
1) Installation Tests, inside the `acceptance/01-install` folder
2) Administrator Tests, inside the `acceptance/administrator` folder

### Installation
Here are the steps that are needed to setup UI tests execution on `localhost`

#### Linux OS
1) Checkout the Project in your document root folder, and follow the [setup guide](https://docs.joomla.org/Special:MyLanguage/J4.x:Setting_Up_Your_Local_Environment).
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

These are the Joomla 4 API (webservices) tests. To run these tests on OSX you will need to install the GNU Sed package with `brew install gnu-sed`

### Installation

1) Checkout the Project in your document root folder, and follow the [setup guide}(https://docs.joomla.org/Special:MyLanguage/J4.x:Setting_Up_Your_Local_Environment).
2) Copy the file `tests/Codeception/api.suite.dist.yml` to `tests/Codeception/api.suite.yml`. Then edit the REST url in the new file to point it to your localhost url.
3) Edit the file configuration.php. Set `$secret` = `'tEstValue'` - see [drone-api-run.sh](https://github.com/joomla/joomla-cms/blob/d8930208814fb52c0871853cfd9298f70998fd1f/tests/Codeception/drone-api-run.sh#L59).

> Tests with authentication always use the super user credentials for now.

### Running

`libraries/vendor/bin/codecept run api`

You can also run the command with

- `--debug` to get some extended information.
- `--steps` to print step-by-step execution.
- `--fail-fast` to stop after first failure.

See [Codeception Console Commands](https://codeception.com/docs/reference/Commands)
