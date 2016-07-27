Joomla! GSoC 2016 JavaScript Tests [![Analytics](https://ga-beacon.appspot.com/UA-544070-3/joomla-cms/readme)](https://github.com/igrigorik/ga-beacon)
====================

Build Status
---------------------
Travis-CI: [![Build Status](https://travis-ci.org/joomla/joomla-cms.svg?branch=staging)](https://travis-ci.org/joomla/joomla-cms)
Jenkins: [![Build Status](http://build.joomla.org/job/cms/badge/icon)](http://build.joomla.org/job/cms/)

[![Demo on running JavaScript tests](http://img.youtube.com/vi/Tp_mLqMhRuA/0.jpg)](http://www.youtube.com/watch?v=Tp_mLqMhRuA)

What is this?
---------------------
* This is the repository for the Google Summer of Code Joomla JavaScript Testing project.
* Joomla core currently has some custom written JavaScript libraries used in performing various front end tasks.
* This repository is dedicated to the JavaScript tests written for those custom JavaScript libraries in Joomla!.
* The `tests\javascript` directory contains all those JavaScript tests.
* The tests are written using the Jasmine framework and Karma is used as the test runner.

Install dependencies
---------------------
1. Open a command line and navigate to the directory `tests/javascript`
2. Execute command  `npm install`
  * This will install all the dependencies to the tests/javascript/node_modules directory. If a node_modules folder does not exist, a folder will automatically be created by npm.

Starting the Karma server and running the tests
---------------------
* Open a command line and navigate to the directory tests/javascript
* Execute command `npm test`
