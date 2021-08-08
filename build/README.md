# Joomla build tools

Joomla provides a set of tools for managing the static assets dependencies based on popular NodeJS tools and also a couple of PHP scripts that automate the release process.

## Node based tools
The responsibilities of these tools are:
- to copy files from the `node-modules` folder to the `media` folder
- do any transformations on the copied files
- Update the version numbers on the xml files of the editors tinyMCE and Codemirror
- Copy files from the `build/media_source` folder to the `media` folder
- Transform any modern JS to both ES2017 and transpile it to ES5
- Transform any SCSS file to the respective CSS file

For some of these operations some conventions were established, to simplify and speed up the process.

## Javascript
There are three options here:
- Modern Javascript files must have an extension `.es6.js`.
  This allows the ESLint to check the style code, Joomla is using the AirBnB preset https://github.com/airbnb/javascript
  Also it instructs Rollup to do the transforms for ES2017 and then transpile to ES5. This step creates normal and minified files.
  Production code WILL NOT have the `.es6` part part for ES2017+ files but WILL HAVE a `-es5.js` for the ES5 ones

- Web Component Javascript files must have an extension `.w-c.es6.js`.
  This allows the ESLint to check the code and it instructs Rollup to do the transforms for ES2017 and then transpile to ES5. This step creates normal and minified files. The difference with the `.es6` files is that the tools will automate the minification of the css (assuming that the appropriate scss file exists) and injected into the js file into a placeholder `{{CSS_CONTENTS_PLACEHOLDER}}` (ie: `build/media_source/system/js/joomla-core-loader.w-c.es6.js`)
  Production code WILL NOT have the `.w-c.es6` part for ES2017+ files but WILL HAVE a `-es5.js` for the ES5 ones

- Legacy Javascript files must have an extension `.es5.js`.
  This instructs ESLint to skip checking this file
  Also it instructs the tools to create a minified version (production code WILL NOT have the `.es5` part)
	
- Javascript files with only an extension `.js`
  These files will be ignored by the build tools.

## SCSS
- SCSS files starting with `_` will not become entry points for SCSS.
  SCSS files will be transformed to CSS both normal and minified versions

## CSS
- CSS files will only get minified


## NPM commands
- `npm run build:js`: compiles ALL the JS (excluding Bootstrap and Media Manager)
- `npm run build:js -- build/media_source/com_actionlogs`: compiles ALL the JS ONLY in the folder `build/media_source/com_actionlogs`
- `npm run build:css`: compiles ALL the SCSS
- `npm run build:css -- templates/cassiopeia`: compiles ALL the SCSS ONLY in the folder `templates/cassiopeia`
- `npm run build:bs5`: Builds the Bootstrap Javascript components
- `npm run build:com_media`: Builds the Media Manager Vue Application
- `npm run lint:js`: Check the code style for all the Javascript/vue files
- `npm run lint:js -- --fix`: Check and fix the code style for all the Javascript/vue files (might not fix everything)
- `npm run lint:css`: Check the code style for all the SCSS files
- `npm run lint:css -- --fix`: Check and fix the code style for all the SCSS files (might not fix everything)
- `npm run gzip`: Creates `.gz` files for all the `.min.js` and `.min.css`
- `npm run versioning`: Creates the correct version hash for all the assets inside the joomla.asset.json files (excluding templates)

## Working efficiently with the Joomla build tools

Usually when contributing to the project the scope of the contribution should be limited, eg fixing some css bug, a javascript bug or some markup or a bug that involves changes in all these areas. The build tools were created so that you spent less time on compiling assets rather than testing a possible solution. *Embrace the watchers* and let the computer help you succeed faster and safer. There are 2 watcher at the moment: one for the Media Manager (client app based on VueJS) and another one that handles templates and the source (build) folder.

Assuming that you are working on the Media Manager you can run in your terminal (already in the root folder of the Joomla repo) `npm run watch:com_media`. This watcher will automatically recomile the app on every save (there is a debounce of 0.3s so if you have autosave will be clever to reduce the tasks).

Assuming that you are working on the Web Authenication JavaScript you can run in your terminal (already in the root folder of the Joomla repo) `npm run watch -- build/media_source/plg_system_webauthn`. This watcher will automatically recomile any JavaScript file inside the folder `build/media_source/plg_system_webauthn/js` on every save. But there's more, since you asked the watcher to check the parent folder (eg `build/media_source/plg_system_webauthn`) you can also edit the SCSS files in the `build/media_source/plg_system_webauthn/scss` or the css files in `build/media_source/plg_system_webauthn/css`. The same example for editing some SCSS in the cassiopeia template would require a command like: `npm run watch -- templates/cassiopeia/scss`.

Once you get your code doing what it meant to do make sure that you checked that you are not breaking any of the Code Style rules by running `npm run lint:css -- --fix` and `npm run lint:js -- --fix` (the `-- --fix` will try to fix anything that's not trivial).

Happy coding
