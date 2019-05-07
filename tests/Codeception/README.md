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
