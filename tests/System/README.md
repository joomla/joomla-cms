# System Tests in Joomla

The Joomla CMS System Tests are end-to-end (E2E) tests and executed in real browsers
using the [Cypress](https://www.cypress.io) test automation tool.
These tests simulate real user interactions, clicking and navigating like a human would,
to ensure the entire application works as expected.

This document provides an overview of the software architecture, explains how to set up the test environment
and details how to execute and extend tests.
It concludes with solutions for common failure situations in the troubleshooting chapter.


## Software Architecture – Overview

The following software architecture diagram illustrates the Joomla System Tests architecture and provides an overview.
It is simplified to offer an initial understanding. Detailed explanations follow later in this document.

![System Tests Architecture](images/system-tests.svg)

On the left, **Cypress** is running as a [Node.js](https://nodejs.org/) application. The file **`cypress.config.mjs`** is used to configure settings and preferences for running the System Tests in your environment.

In the middle, the **Cypress Test Runner** controls a **Browser** with the **Joomla** application running HTML, CSS, and JavaScript. Also running in the browser context are the **Database Commands**, the **API commands** and the npm packages **[joomala-cypress](https://github.com/joomla-projects/joomla-cypress/)** and
**[smtp-tester](https://www.npmjs.com/package/smtp-tester)**.

The **Joomla** CMS server software is depicted on the right. It runs with PHP on the **Web Server** and includes
several key components: the public **User-Frontend**, the administrator **Admin-Backend**, the **API**, and the **Web-Installer**. These components and their interactions will be detailed later in the document.
The file **`configuration.php`** is used to configure settings for Joomla server software.

Joomla uses a **Database**, and the System Tests do as well.


## Installation

Joomla System Tests work on different operating systems such as macOS, desktop Linux distributions like Ubuntu,
and Windows (using WSL 2 or a framework like Laragon). They also work well with Docker containers.
You will need a Web Server and a database like MariaDB running.
Before getting started, please ensure you have the following prerequisites installed:
[PHP](https://www.php.net/), [Git](https://git-scm.com/), [npm](https://www.npmjs.com/),
and [Composer](https://getcomposer.org/).

The following steps are required for the installation of the CMS System Tests.

1. Clone Joomla into an empty folder (e.g. `/var/www/html`) where it can be served by a Web Server
```
git clone --depth 1 https://github.com/joomla/joomla-cms /var/www/html
```
2. Install the PHP and JavaScript dependencies
```
cd /var/www/html
composer install
npm ci
```
3. Create the Cypress configuration file from the distribution template.
```
cp cypress.config.dist.mjs cypress.config.mjs
```
4. Adjust the parameter `baseUrl` in the `cypress.config.mjs` file, it should point to the Joomla base URL.
5. Adapt the env variables in the file `cypress.config.mjs`, they should point to the site, user data and database environment. Ensure that the `smtp_port` is not in use on your system.


## Running System Tests

After installation, you can start the Joomla System Tests with headless Cypress. The test suite starts with Joomla Web-Installer as the first test step.
```
npm run cypress:run
```
:point_right: In case of errors, see [Troubleshooting](#Troubleshooting) at the end.

You can execute single test specs, e.g. to run the installation step only.
```
npx cypress run --spec tests/System/integration/install/Installation.cy.js
```

You can run multiple test specs separated by commas and use patterns.
For example, to run all tests without the installation step:
```
npx cypress run --spec 'tests/System/integration/{administrator,site,api,plugins}/**/*.cy.js'
```

> [!NOTE]
> Cypress has a nice GUI that lists all the existing tests and can launch a browser where the tests are executed.
> It is really helpful to be able to rewind in case of problems and see how the automatic browser works.
> The Cypress GUI also displays the Cypress log output, providing real-time feedback on the test execution process.
> To open the Cypress GUI, run the following command.
> ```
> npm run cypress:open
> ```

If you are running System Tests, you will see `console.log()` outputs from Cypress Tasks in the Node.js environment. If you would like to see `console.log()` output from the browser in headless mode as well, you can use the Electron web browser and set the following environment variable:
```
export ELECTRON_ENABLE_LOGGING=1
npm run cypress:run --browser electron
```


## Software Architecture – More Detailed

Since many interactions in System Tests are involved, the following image illustrates 10 simplified interactions,
which are numbered and described below.

![System Tests Architecture with 10 Interactions](images/system-tests-interactions.svg)

1. **Cypress** starts the **Browser** and runs **Cypress Test Runner** to control Joomla running in the browser and access the DOM.
2. **Joomla** software running in the browser sends requests to the **Web Server** and receives responses just as it would during normal use, even without tests.
3. The Cypress custom **API commands** (described later) interact with the Joomla **API** on the Web Server.
4. Cypress **Tasks** are used to execute code within the Cypress Node.js context. These tasks are triggered by the Cypress Test Runner, which runs in the browser, and are typically used for operations like
interacting with the file system.
5. Joomla on the Web Server interacts with the **Database** as it normally would, without running any tests.
6. System Tests has Cypress custom **Database Commands** (described later) to interact with the database.
7. The file `cypress.config.mjs` is read by **Cypress** and used to configure settings and preferences for running the System Tests in your environment.
8. The Joomla installation is initiated by the test spec [Installation.cy.js](integration/install/Installation.cy.js),
which is the first test executed in the overall test suite.
This test spec deletes the Joomla configuration file, and since the `configuration.php` file no longer exists,
the following Joomla Web-Installer call starts the installation process, including database creation.
To ensure that this initial test spec runs correctly, the `installation` folder must not be deleted,
allowing the System Tests to be executed multiple times.
After the Joomla Web-Installer completes, [Installation.cy.js](integration/install/Installation.cy.js)
modifies some parameters in the `configuration.php` file, such as SMTP settings or the API `$secret`.
9. Joomla Web-Installer creates `configuration.php` file. For security reasons, the file mask is set to read-only (444).
10. Joomla reads the settings for Joomla server software from file `configuration.php` like Database connection or
SMTP configuration.

The used npm package "Helpers for using Cypress with Joomla for testing" **[joomala-cypress](https://github.com/joomla-projects/joomla-cypress/)** helps in writing the Cypress tests for Joomla in extending the Cypress API with custom commands.

The **[smtp-tester](https://www.npmjs.com/package/smtp-tester)** npm package creates an SMTP server that listens
on the `smtp_port` specified in `cypress.config.mjs` during test runtime.
This server accepts connections, receives emails, and provides the capability to check the received emails during the test.

> [!IMPORTANT]
> The Cypress custom commands and the tasks are asynchronous operations, meaning their execution time is uncertain.
> Therefore, you must always chain them to ensure they are completed before the function returns.
> Welcome to the async land of Node.js. :sweat_smile:


## Create New Tests

To create new tests, create a `cy.js` file in a new folder which matches the following pattern
(replace *foo* with the extension name to test):

- Component tests belong in a folder tests/System/integration/{site or administrator}/components/com_*foo*
- Module tests belong in a folder tests/System/integration/{site or administrator}/modules/mod_*foo*
- Plugin tests belong in a folder tests/System/integration/plugins/{type}/*foo*
- API tests belong in a folder tests/System/integration/api/com_*foo*

> [!TIP]
> Probably the easiest way is to copy an existing file and adapt it to the extension which should be tested.


## Test Development

Tests should be:
- Repeatable
- Not depend on other tests
- Small
- Do one thing


### Tasks

The Joomla System Tests come with some convenient [Cypress Tasks](https://docs.cypress.io/api/commands/task) which execute actions on the server in a node environment. That's why the `cy.` namespace is not available. The following Cypress Tasks are available, served by the file [tests/System/plugins/index.js](/tests/System/plugins/index.js):

- **queryDB** – Executes a query on the database
- **cleanupDB** – Deletes the inserted items from the database
- **writeRelativeFile** – Writes a file relative to the CMS root folder
- **deleteRelativePath** – Deletes a file or folder relative to the CMS root folder
- **copyRelativeFile** – Copies a file relative to the CMS root folder
- **startMailServer** – Starts the smtp-tester SMTP server
- **getMails** – Get received mails from smtp-tester
- **clearEmails** – Clear all smtp-tester received mails

The following code in a test executes the writing file task with parameters:
```JavaScript
 cy.task('writeRelativeFile', { path: 'images/dummy.text', content: '1' }).then(() =>  { ... })
```
:point_right: As each task is asynchronous and must be chained, the result includes `.then()`.


### Commands

We are using [custom commands](https://docs.cypress.io/api/cypress-api/custom-commands) to enhance Cypress
with reusable code snippets for the System Tests.
These commands can be used to create objects in the database or to call the API.
Since Cypress doesn't support namespaces for commands, we prefix them in the function name.
Therefore, a Database Command always starts with `db_`, and an API command with `api_`.

Commands can be called like a normal function, for example there is a command to create article in the database:
```JavaScript
cy.db_createArticle({ title: 'automated test article' }).then((id) => { ... })`
```
These commands are executed in the browser where the `cy.` namespace is available.


#### Database Commands

The Database Commands create items in the database like articles or users. They are asynchronous and must be chained like:
```JavaScript
cy.db_createArticle({ title: 'automated test article' }).then((id) => { ... })`
```

The following commands are available and are served by the file [tests/System/support/commands/db.mjs](/tests/System/support/commands/db.mjs):

- **db_createArticle** – Creates an article and returns the id
- **db_createBanner** – Creates a banner and returns the id
- **db_createBannerClient** – Creates a banner client and returns the id
- **db_createCategory** – Creates a category and returns the id
- **db_createContact** – Creates a contact and returns the id
- **db_createField** – Creates a field and returns the id
- **db_createFieldGroup** – Creates a field group and returns the id
- **db_createMenuItem** – Creates a menu item and returns the id
- **db_createMenuType** – Creates a menu type and returns the id
- **db_createModule** – Creates a module and returns the id
- **db_createNewsFeed** – Creates a news feed and returns the id
- **db_createPrivacyConsent** – Creates a private consent and returns the id
- **db_createPrivacyRequest** – Creates a private request and returns the id
- **db_createTag** – Creates a tag and returns the id
- **db_createUser** – Creates a user entry and returns the id
- **db_createUserGroup** – Creates a user group and returns the id
- **db_createUserLevel** – Creates a user access level  and returns the id
- **db_enableExtension** – Sets the enabled status for the given extension
- **db_getUserId** – Returns the id of the currently logged in user
- **db_updateExtensionParameter** – Sets the parameter for the given extension


#### API commands

The API commands make API requests to the CMS API endpoint `/api/index.php/v1`.
They are asynchronous and must be chained like:
```JavaScript
cy.api_get('/content/articles').then((response) => { ... })`
```
The response is an object from the [Cypress request command](https://docs.cypress.io/api/commands/request).
The following commands are available and are served by the file
[tests/System/support/commands/api.mjs](/tests/System/support/commands/api.mjs):

- **api_get** – HTTP GET request for given path
- **api_post** – HTTP POST request for given path and body
- **api_patch** – HTTP PATCH request for given path and body
- **api_delete** – HTTP DELETE request for given path
- **api_getBearerToken** – Returns the bearer token, creates user entry if needed
- **api_responseContains** – Checks if the given attribute in the response contains the specified value


### Developer Tips

#### Running a Single Test

If you wish to run only one single test from a test spec file for debugging, you can add `.only` to the test function:
```JavaScript
it.only('running only this test', () => {
    ...
})
```
For more details, see the [Cypress docu, Excluding and Including Tests](https://docs.cypress.io/guides/core-concepts/writing-and-organizing-tests#Excluding-and-Including-Tests).


#### Wait After Action

When developing tests with Cypress, it can be helpful to insert delays for debugging, allowing you to observe the status.
For example:
```JavaScript
cy.wait(20000); // waits for 20 seconds
```
:no_good_woman: Do not use `wait()` regularly in tests.

## Troubleshooting

### Errors 'EACCES: permission denied' or 'EPERM: operation not permitted'

If the Cypress installation step or the entire test suite is executed by a non-root user, the following error may occur:
```
1) Install Joomla
       Install Joomla:
       CypressError: `cy.task('writeRelativeFile')` failed with the following error:
       > EACCES: permission denied, open './configuration.php'
```
Or on Microsoft Windows you will see:
```
       > EPERM: operation not permitted, open 'C:\laragon\www\joomla-cms\configuration.php'
```

The reason for this error is that Cypress first creates the Joomla file `configuration.php` via the Web Server with file mask set read-only (444).
Subsequently, some of the parameters in this file are adopted by Cypress under the current user.
If the Web Server and Cypress are run by different users, this can lead to file access issues.

:point_right: You have to give the user running Cypress the permission to write `configuration.php`
e.g. with the command `sudo` on macOS, Linux or Windows WSL 2:
```
sudo npm run cypress:run
```

If the `root` user does not have a Cypress installation, you can use the Cypress installation cache of the current user:
```
sudo CYPRESS_CACHE_FOLDER=$HOME/.cache/Cypress npm run cypress:run
```


### Error 'EADDRINUSE: address already in use'

If the used SMTP server port is already in use you will see an error like:
```
    Your configFile threw an error from: cypress.config.mjs

    We stopped running your tests because your config file crashed.

    Error: listen EADDRINUSE: address already in use :::1025
```

:point_right: Configure a different, unused port in the `cypress.config.mjs` file as `smtp_port`.

:point_right: If you use `npx` instead of `npm`, you may see `Your configFile threw an error from: cypress.config.js`,
but you still need to configure `cypress.config.mjs` file.

### Timeout Error on Slow Machines

If you encounter the following error while running the System Tests on slow machines:

```
     AssertionError: Timed out retrying after 4000ms: Expected to find element
```

:point_right: You can increase the default 4 second waiting time in the cypress.config.mjs file:

```JavaScript
    export default defineConfig({
      defaultCommandTimeout: 20000, // sets the waiting time to 20 seconds
      ...
    }
```

## Docker
The system tests can also be executed in headless mode with docker compose. The following command does a cleanup and then starts the system tests from the current docker-compose.yml file:

`docker compose down && docker compose up system-tests`

The database is used with a temporary filesystem, so the data always gets deleted when the tests are started, therefor the installation test must be performed as the first step. The webserver is accessible on the host from http://localhost:8080 or https://localhost:8443 and PHPMyAdmin on http://localhost:8081.
