Joomla! GSoC 2016 JavaScript Tests [![Analytics](https://ga-beacon.appspot.com/UA-544070-3/joomla-cms/readme)](https://github.com/igrigorik/ga-beacon)
====================

Build Status
---------------------
Travis-CI: [![Build Status](https://travis-ci.org/joomla/joomla-cms.svg?branch=staging)](https://travis-ci.org/joomla/joomla-cms)
Jenkins: [![Build Status](http://build.joomla.org/job/cms/badge/icon)](http://build.joomla.org/job/cms/)

What is this?
---------------------
* This is the repository for the Google Summer of Code Joomla JavaScript Testing project.
* Joomla core currently has some custom written JavaScript libraries used in performing various front end tasks.
* This repository is dedicated to the JavaScript tests written for those custom JavaScript libraries in Joomla!.
* The `tests\javascript` directory contains all those JavaScript tests.
* The tests are written using the Jasmine framework and Karma is used as the test runner.

Before you can run the tests, you need to install some programs on your local workstation, as documented below.

Prerequisites
---------------------
* The testing environment requires your local machine to have Node.js installed in it.
* To install node.js, please go to the node.js [official website](https://nodejs.org/en/), download the respective setup for your operating system and install it by following the installation wizard.
* The current testing setup uses Firefox as the browser to run the tests. Please make sure You have Firefox installed in you local machine.

Install dependencies
---------------------
1. Open a command line and navigate to the directory `tests/javascript`
2. Execute command  `npm install`
  * This will install all the dependencies to the tests/javascript/node_modules directory. If a node_modules folder does not exist, a folder will automatically be created by npm.

Starting the Karma server and running the tests
---------------------
* Open a command line and navigate to the directory tests/javascript
* Execute command `npm test`
