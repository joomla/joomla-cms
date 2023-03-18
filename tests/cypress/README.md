# System tests in Joomla

The CMS system tests are executed in real browsers and are using the [cypress.io](https://www.cypress.io) framework. This article describes how to setup your local development environment to execute the existing tests and add new ones.

## Installation
A couple of steps are needed before the CMS system tests can be executed on your system.

1. Clone Joomla into a folder where it can be served by a web server
2. Install the PHP and Javascript dependencies by running the following commands:
   1. `composer install`
   2. `npm ci`
3. Copy the cypress.config.dist.js to cypress.config.js in the root of your joomla folder
4. Adapt the env variables in the file cypress.config.js, they need to point to a database server where joomla can be installed
5. Ensure your system has all the required dependencies according to their [docs article](https://docs.cypress.io/guides/getting-started/installing-cypress)
6. Run the command `npm run cypress:install`

## Run the existing tests
Cypress has a nice gui which lists all the existing tests and is able to launch a browser where the tests are executed. To open the cypress gui, run the following command:

`npm run cypress:open`

## Add your own tests
To add your own tests, create a cy.js file to a new folder which matches the following pattern (replace foo with your extension name):

- Component tests do belong to a folder {site or administrator}/components/com_foo
- Module tests do belong to a folder {site or administrator}/modules/mod_foo
- Plugins tests do belong to a folder plugins/{type}/foo
- API tests do belong to a folder api/com_foo

Probably the easiest way is to copy an existing file.

## Some developer information
Tests should be
- repeatable
- not depend on other tests
- small
- do one thing

The CMS tests do come some some convenient cypress tasks which are executing actions on the server. The following tasks are available:

- **queryDB** Executes a query on the database
- **cleanupDB** does some cleanup, is executed automatically after every test
- **writeFile** writes a file relative to the CMS root folder
- **deleteFolder** deletes a folder relative to the CMS root folder

With the following code can be run `cy.task('writeFile', { path: 'images/dummy.text', content: '1' })`. 
