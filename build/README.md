# Joomla Build Tools

Joomla provides a set of tools for managing static assets and dependencies based on popular NodeJS tools and a couple of PHP scripts that automate the release process.

## Node Based Tools
The responsibilities of these tools are:
- To copy files from the `node-modules` folder to the `media` folder.
- Do any transformations on the copied files.
- Copy files from the `media_source/` folder to the `media` folder.
- Transform any modern JS to ES2017 and transpile it to ES5.
- Transform any SCSS file to the respective CSS file.

For some of these operations, conventions were established to simplify and speed up the process.

Read more at [CMS Media source](../media_source/README.md).


## NPM Commands

- `npm run builders-list` Show list of available builders in order of execution.
- `npm run build` Build assets with production env. Extra arguments are required. Check [CMS Media source](../media_source/README.md#building-assets).
- `npm run build:dev` Build assets with development env. Extra arguments are required. Check [CMS Media source](../media_source/README.md#building-assets).
- `npm run watch` Start files watcher for specified asset with production env\
Example `npm run watch -- -n com_content` for `com_content` assets.\
Example `npm run watch -- --all` for all assets.
- `npm run watch:dev` Start files watcher for specified asset with development env.
- `npm run lint:js` Checks the code style for the JavaScript/Vue files
- `npm run lint:testjs` Checks the code style for the JavaScript/Vue files under `tests/System` used for testing.
- `npm run lint:css` Checks the code style for all SCSS files.
- `npm run gzip` Creates `.gz` files for all the `.min.js` and `.min.css`.


## Working Efficiently with the Joomla Build Tools

Usually, the scope of a single contribution to the project should be limited. For example: fixing a CSS bug, a Javascript bug, some Markup, or a bug that involves changes in all these areas. The build tools were created so that you spend less time on compiling assets than testing a possible solution.

Once you get your code doing what it is meant to do, make sure that you check you are not breaking any of the Code Style rules by running `npm run lint:css -- --fix` and `npm run lint:js -- --fix` (the `-- --fix` will try to fix anything that's not trivial).

Happy coding
