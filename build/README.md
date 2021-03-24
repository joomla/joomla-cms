# Joomla build tools

Joomla provides a set of tools for managing the static assets dependencies based on popular NodeJS tools and also a couple of PHP scripts that automate the release process.

## Node based tools
The responsibilities of these tools are:
- to copy files from the `node-modules` folder to the `media` folder
- do any transformations on the copied files
- Update the version numbers on the xml files of the editors tinyMCE and Codemirror
- Copy files from the `build/media_source` folder to the `media` folder
- Transform any modern JS to both ES2017 and transpile it to ES5
- Transform any SCSS file to the respected CSS file

For some of these operations some conventions were established, to simplify and speed up the process.

## Javascript
There are three options here:
- Modern Javascript files should have an extension `.es6.js`.
  This allows the ESLint to check the style code, Joomla is using the AirBnB preset https://github.com/airbnb/javascript
  Also it instructs Rollup to do the transforms for ES2017 and then transpile to ES5. This step creates normal and minified files.
  Production code WILL NOT have the `.es6` part part for ES2017+ files but WILL HAVE a `-es5.js` for the ES5 ones

- Legacy Javascript files should have an extension `.es5.js`.
  This instructs ESLint to skip checking this file
  Also it instructs the tools to create a minified version (production code WILL NOT have the `.es5` part)

## SCSS
- SCSS files starting with `_` will not become entry points for SCSS.
  SCSS files will be transformed to CSS both normal and minified versions

## CSS
- CSS files will only get minified
