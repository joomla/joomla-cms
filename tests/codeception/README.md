[Browser Automated Tests for Joomla! CMS(covering Users and Content features - GSoC 16)](https://summerofcode.withgoogle.com/projects/#5724182314745856)
===

Abstract
---
System development nowadays more than ever starts to look for automated test methods. There are several main drivers for this trend:
+ Need for faster design‐develop‐test‐analysis cycle
+ Push for higher quality
+ Increasing complexity of systems and their integration and last but not least
+ Ever‐rising costs of manual testing
 
Automation Testing means using an automation tool to execute test case suite. The automation software can also enter test data into the System Under Test, compare expected and actual results and generate detailed test reports.

Test Automation demands considerable investments of money and resources. Successive development cycles will require execution of same test suite repeatedly. Using a test automation tool it's possible to record this test suite and re-play it as required. Once the test suite is automated, no human intervention is required.

### Installation

1. Clone this repository

2. Install `composer` in your system. Read more about [how to install composer](https://getcomposer.org/doc/00-intro.md) here.

3. Install composer packages using the following steps from root directory of this project.
We are using `composer.json` file for `tests/codeception` folder, so that you will have to run composer install from the tests directory.

    ```bash
    $ cd tests/codeception && composer install
    ```

4. Copy `tests/codeception/acceptance.suite.dist.yml` to `tests/codeception/acceptance.suite.yml` and change the settings according to your webserver.

    ```
    $ cp acceptance.suite.dist.yml acceptance.suite.yml
    ```

5. Get back to project root directory using `$ cd ..` twice.

### Run tests

To run the tests please execute the following commands. We are using [Robo.li](http://robo.li/) to execute 
[PhpUnit](https://phpunit.de/) and Selenium based [Codeception](http://codeception.com/for/joomla) test suits.

#### To execute all the test features you should use.

```bash
$ tests/codeception/vendor/bin/robo run:tests
```

#### You can individual run `feature` using following command.

**_Linux & Mac_**
```bash
$ tests/codeception/vendor/bin/robo run:test
```

**_Windows_**
```cmd
$ tests\codeception\vendor\bin\robo run:test
```

If you want to see the steps then you can use `--steps` option of codeception. 
Check [full codecept command list here](http://codeception.com/docs/reference/Commands#Run)

**Note**: You can modify the timeout time by setting the value of the **TIMEOUT** constant lower for fast machines and higher for slow computers.
The constant located in the file `tests/codeception/acceptance/_bootstrap.php`

Changing the browser the tests are running with?
---
In your acceptance.suite.yml just change the browser name. Possible values are firefox, chrome, internet explorer and MicrosoftEdge. 

Note: If you are running Windows Insiders builds, then you need to set MicrosoftEdgeInsiders to true. 
