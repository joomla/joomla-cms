# System tests in Joomla

The CMS system tests are executed in real browsers and are using the [cypress.io](https://www.cypress.io) framework. This article describes how to setup a local development environment to execute the existing tests and create new ones.

## Installation
A couple of steps are needed before the CMS system tests can be executed on the system.

1. Clone Joomla into a folder where it can be served by a web server
```
git clone --depth 1 https://github.com/joomla/joomla-cms 
```
2. Install the PHP and Javascript dependencies by running the following commands:
```
cd joomla-cms
composer install
npm ci
```
3. Copy the `cypress.config.dist.js` to `cypress.config.js` in the root of the joomla folder
4. Adjust the `baseUrl` in the `cypress.config.js` file, it should point to the Joomla base URL
5. Adapt the env variables in the file `cypress.config.js`, they should point to the site, user data and database environment
6. Ensure the system has all the required dependencies according to the Cypress [documentation](https://docs.cypress.io/guides/getting-started/installing-cypress)
7. Install Cypress
```
npm run cypress:install
```
8. Run Joomla installation with headless Cypress
```
npx cypress run --spec tests/System/integration/install/Installation.cy.js
```
:point_right: In the case of `EACCES` or `EPERM` error, see troubleshooting at the end.

## Run the existing tests
You can use Cypress headless:
```
npx cypress run
```

And Cypress has a nice GUI which lists all the existing tests and is able to launch a browser where the tests are executed. To open the Cypress GUI, run the following command:
```
npx cypress open
```

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

- **queryDB** executes a query on the database
- **cleanupDB** deletes the inserted items from the database
- **writeFile** writes a file relative to the CMS root folder
- **deleteFolder** deletes a folder relative to the CMS root folder
- **getFilePermissions** get file permissions
- **changeFilePermissions** change file permissions

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

# Troubleshooting
## `EACCES: permission denied` or `EPERM: operation not permitted`

If the Cypress installation step or the entire test suite is executed by a non-root user, the following error may occur:
```
1) Install Joomla
       Install Joomla:
       CypressError: `cy.task('writeFile')` failed with the following error:
       > EACCES: permission denied, open './configuration.php'
```
Or on Microsoft Windows you will see:
```
       > EPERM: operation not permitted, open 'C:\laragon\www\joomla-cms\configuration.php'
```

The reason for this is that the Cypress installation first creates the Joomla file `configuration.php`
from the web server and then some of the parameters in the file are configured with Cypress by the current user.
This can cause a file access problem if different users are used for the web server and the execution of Cypress.

You have to give the user running Cypress the right to write `configuration.php`
e.g. with the command `sudo` on macOS, Linux or Windows WSL 2:
```
sudo npx cypress run --spec tests/System/integration/install/Installation.cy.js
```

If the `root` user does not have a Cypress installation, you can use the Cypress installation cache of the current user:
```
sudo CYPRESS_CACHE_FOLDER=$HOME/.cache/Cypress npx cypress run --spec tests/System/integration/install/Installation.cy.js
```

## Errors from test spec `api/com_media/Files.cy.js`
If you are using `sudo` and running the `com_media/Files` API test specification, you may see errors like:
```
  > 404: Not Found
  > 500: Internal Server Error
```
Reason for this is, that the Cypress test creates the directory `images/test-dir` as `root` user and prevents web server user `www-data` from creating files inside. You have to set `umask` additionally:
```
sudo bash -c "umask 0 && CYPRESS_CACHE_FOLDER=$HOME/.cache/Cypress npx cypress run --spec tests/System/integration/api/com_media/Files.cy.js"
```
Or if `root` user has Cypress installed:
```
sudo bash -c "umask 0 && npx cypress run --spec tests/System/integration/api/com_media/Files.cy.js"
```
Or to run the System test suite:
```
sudo bash -c "umask 0 && npx cypress run"
```
</details>
