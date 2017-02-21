[Browser Automated Tests for Joomla! CMS(covering Users and Content features - GSoC 16)](https://summerofcode.withgoogle.com/projects/#5724182314745856)
===

Abstract
---
System development nowadays more than ever starts to look for automated test methods. There are several main drivers for this trend,
+ Need for faster design‐develop‐test‐analysis cycle
+ Push for higher quality
+ Increasing complexity of systems and their integration and last but not least
+ Ever‐rising costs of manual testing
 
Automation Testing means using an automation tool to execute test case suite. The automation software can also enter test data into the System Under Test, compare expected and actual results and generate detailed test reports.


Test Automation demands considerable investments of money and resources. Successive development cycles will require execution of same test suite repeatedly. Using a test automation tool it's possible to record this test suite and re-play it as required. Once the test suite is automated, no human intervention is required.

BDD Testing with Gherkin and Codeception
---

### What is Gherkin – BDD Language?
=======
What is this?
---------------------
* This is a Joomla! 3.x installation/upgrade package.
* Joomla's [Official website](https://www.joomla.org).
* Joomla! 3.7 [version history](https://docs.joomla.org/Joomla_3.7_version_history).
* Detailed changes are in the [changelog](https://github.com/joomla/joomla-cms/commits/master).

What is Joomla?
---------------------
* [Joomla!](https://www.joomla.org/about-joomla.html) is a **Content Management System** (CMS) which enables you to build websites and powerful online applications.
* It is a simple and powerful web server application which requires a server with PHP and either MySQL, PostgreSQL or SQL Server to run. You can find [full technical requirements here](https://downloads.joomla.org/technical-requirements).
* Joomla! is **free and Open Source software** distributed under the GNU General Public License version 2 or later.

Is Joomla! for you?
---------------------
* Joomla! is [the right solution for most content web projects](https://docs.joomla.org/Portal:Learn_More).
* View Joomla's [core features here](https://www.joomla.org/core-features.html).
* Try it out for yourself in our [online demo](https://demo.joomla.org).
>>>>>>> upstream/staging

* Gherkin is a **human-readable** language for system behaviour description.
* Gherkin is a _natural_ language for testing that **Codeception** uses to define test cases.
* The test is written in plain `English` which is common to all the domains of project team.
* Test cases were designed to be **non-technical** and **human readable**, and **collectively describes**.
* This test is structured that makes it capable of being read in an automated way. 
* Gherkin file have a `.feature` extention.

### Benefits of Gherkin?


### Main Keywords In Gherkin

* Feature
* Scenario
* Given, When, Then, And, But (Steps)
* Background
* Scenario outline
* Examples


### Example
Create a .feature using command `tests/codeception/vendor/bin/codecept generate:feature acceptance content`

File `content.feature` contains,

```gherkin
Feature: content
  In order to manage content article in the web
  As an owner
  I need to create modify trash publish and Unpublish content article

  Background:
    Given Joomla CMS is installed
    When Login into Joomla administrator with username "admin" and password "admin"
    Then I see administrator dashboard

  Scenario: Create an Article
    Given There is a add content link
    When I create new content with field title as "My_Article" and content as a "This is my first article"
    And I save an article
    Then I should see the "Article successfully saved." message
```
Generate snippets of .feature file using command `tests/codeception/vendor/bin/codecept gherkin:snippets acceptance`

```snippets
/**
     * @Given There is a add content link
     */
     public function thereIsAAddContentLink()
     {
        throw new \Codeception\Exception\Incomplete("Step `There is a add content link` is not defined");
     }
     
    /**
     * @When I create new content with field title as :arg1 and content as a :arg2
     */
     public function iCreateNewContentWithFieldTitleAsAndContentAsA($arg1, $arg2)
     {
        throw new \Codeception\Exception\Incomplete("Step `I create new content with field title as :arg1 and content as a :arg2` is not defined");
     }
     
    /**
     * @When I save an article
     */
     public function iSaveAnArticle()
     {
        throw new \Codeception\Exception\Incomplete("Step `I save an article` is not defined");
     }
     
```
Copy the all snippets and put in stepobject file

Create a stepobject file using command `tests/codeception/vendor/bin/codecept generate:stepobject acceptance Administrator/content`

Define your step file path in `acceptance.suit.yml file` 

For Example `- Step\Acceptance\Administrator\Content`

### Installation

1. Clone this repository using command below or download source from [here](https://github.com/joomla-projects/gsoc16_browser-automated-tests/archive/staging.zip)

    ```bash
    $ git clone git@github.com:joomla-projects/gsoc16_browser-automated-tests.git
    ```

2. Install `composer` in your system. Read more about [how to install composer](https://getcomposer.org/doc/00-intro.md) here.

3. Install composer packages using following steps from root directory of this project.
_We are using `composer.json` file for `tests/codeception` folder, so that you will have to run composer install from tests directory._

    ```bash
    $ cd tests/codeception && composer install
    ```

4. Copy `tests/codeception/acceptance.suite.dist.yml` to `tests/codeception/acceptance.suite.yml` and change settings according to your webserver.

    ```
    $ cp acceptance.suite.dist.yml acceptance.suite.yml
    ```

5. Get back to project root direcoty using `$ cd ..` twice.

### Run tests

To run the tests please execute the following commands. We are using [Robo.li](http://robo.li/) to execute [PhpUnit](https://phpunit.de/) based [Codeception](http://codeception.com/for/joomla) test suits. (As we are using Selenium WebDriver 2.53.1 at the moment, a Firefox version above 47.0.1 might be causing issues.)

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

Or you can manually run them using codecept command. Check the following example:

**_Linux & Mac_**
```bash
$ ./tests/codeception/vendor/bin/codecept run tests/codeception/acceptance/users.feature
```

**_Windows_**
```cmd
$ tests\codeception\vendor\bin\codecept run tests/codeception/acceptance/users.feature
```

If you want to see steps then you can use `--steps` option of codeception. Check [full codecept command list here](http://codeception.com/docs/reference/Commands#Run)_

**Note**:You can modify the timeout time by setting the value of **TIMEOUT** constant lower for fast machines and higher for slow computers. The constant located in the file `tests/codeception/acceptance/_bootstrap.php`

Changing the browser the tests are running with?
---
In your acceptance.suite.yml just change the browser name. Possible values are firefox, chrome, internet explorer and MicrosoftEdge. 

Note: If you are running Windows Insiders builds, then you need to set MicrosoftEdgeInsiders to true. 

Do you have suggestions?
---
Please create an issue here https://github.com/joomla-projects/gsoc16_browser-automated-tests/issues we will be happy to discuss and improve project.

Mentors
---
+ Javier Gomez
+ Yves Hoppe
+ Niels Braczek
 
Copyright
---------------------
* Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
* [Special Thanks](https://docs.joomla.org/Joomla!_Credits_and_Thanks)
* Distributed under the GNU General Public License version 2 or later
* See [License details](https://docs.joomla.org/Joomla_Licenses)