# System tests in Joomla

The CMS system tests are executed in real browsers and are using the [cypress.io](https://www.cypress.io) framework. This article describes how to setup a local development environment to execute the existing tests and create new ones.

## Installation
A couple of steps are needed before the CMS system tests can be executed on the system.

1. Clone Joomla into a folder where it can be served by a web server
2. Install the PHP and Javascript dependencies by running the following commands:
   1. `composer install`
   2. `npm ci`
3. Copy the cypress.config.dist.js to cypress.config.js in the root of the joomla folder
4. Adjust the baseUrl in the cypress.config.js file, it should point to the Joomla base url
5. Adapt the env variables in the file cypress.config.js, they should point to the site, user data and database environment
6. Ensure the system has all the required dependencies according to the Cypress [documentation](https://docs.cypress.io/guides/getting-started/installing-cypress)
7. Run the command `npm run cypress:install`

## Run the existing tests
Cypress has a nice gui which lists all the existing tests and is able to launch a browser where the tests are executed. To open the cypress gui, run the following command:

`npm run cypress:open`

## Create new tests
To Create new tests, create a cy.js file in a new folder which matches the following pattern (replace foo with the extension name to test):

- Component tests belong in a folder {site or administrator}/components/com_foo
- Module tests belong in a folder {site or administrator}/modules/mod_foo
- Plugin tests belong in a folder plugins/{type}/foo
- API tests belong in a folder api/com_foo

Probably the easiest way is to copy an existing file and adapt it to the extension which should be tested.

## Some developer information
Tests should be:
- Repeatable
- Not depend on other tests
- Small
- Do one thing

### Tasks

The CMS tests come with some convenient [cypress tasks](https://docs.cypress.io/api/commands/task) which execute actions on the server in a node environment. That's why the `cy.` namespace is not available. The following tasks are available, served by the file tests/System/plugins/index.js:

- **queryDB** Executes a query on the database
- **cleanupDB** does some cleanup, is executed automatically after every test
- **writeFile** writes a file relative to the CMS root folder
- **deleteFolder** deletes a folder relative to the CMS root folder

With the following code in a test a task can be executed `cy.task('writeFile', { path: 'images/dummy.text', content: '1' })`. Each task is asynchronous and must be chained, so to get the result a `.then(() => {})` must follow when executing a task.

### Commands
Commands are reusable code snippets which can be used in every test. Cypress allows to [add custom commands](https://docs.cypress.io/api/cypress-api/custom-commands) so we can use them across our tests. They can be used to create objects in the database or to call an API. As cypress doesn't support namespaces for commands they must be prefixed with the file name and an underscore. So a database command starts always with db_ and an API one with api_.

Commands can be called like a normal function, for example there is a command `db_createArticle`. We can use it like `cy.db_createArticle({ title: 'automated test article' })`. These commands are executed in the browser where the `cy.` namespace is available.

#### Database commands
The database commands create items in the database like articles or users. They are asynchronous and must be chained like `cy.db_createArticle({ title: 'automated test article' }).then((id) => ... the test)`. The following commands are available and are served by the file tests/System/support/commands/db.js:

- **db_createArticle** creates an article and returns the id
- **db_createContact** creates a contact and returns the id
- **db_createBanner** creates a banner and returns the id
- **db_createMenuItem** creates a menu item and returns the id
- **db_createModule** creates a module and returns the id
- **db_createUser** creates a user and returns the id

#### API commands
The API commands make API requests to the CMS API endpoint `/api/index.php/v1`. They are asynchronous and must be chained like `cy.api_get('/content/articles').then((response) => ... the test)`. The response is an object from [the cypress request command](https://docs.cypress.io/api/commands/request). The following commands are available and are served by the file tests/System/support/commands/api.js:

- **api_get** add the path as argument
- **api_post** add the path and content for the body as arguments
- **api_patch** add the path and content for the body as arguments
- **api_delete** add the path as argument
- **api_getBearerToken** returns the bearer token and no request object
