/**
 * Command line helper
 *
 * To get the complete functional media folder please run
 *
 * npm ci
 *
 * For dedicated tasks, please run:
 * node build.js --build-pages  === will create the error pages (for incomplete repo build PHP+NPM)
 * node build.js --copy-assets  === will clean the media/vendor folder and then will populate the folder from node_modules
 * node build.js --compile-js   === will transpile ES6 files and also uglify the ES6,ES5 files
 * node build.js --compile-css  === will compile all the scss defined files and also create a minified version of the css
 * node build.js --gzip  === will create gzip files for all the minified stylesheets and scripts.'
 */

// eslint-disable-next-line import/no-extraneous-dependencies
const Program = require('commander');
// eslint-disable-next-line import/no-extraneous-dependencies

// Joomla Build modules
const errorPages = require('./build-modules-js/error-pages.es6.js');
const init = require('./build-modules-js/init.es6.js');
const compileCSS = require('./build-modules-js/compilecss.es6.js');
const compileJS = require('./build-modules-js/compilejs.es6.js');
const minifyVendor = require('./build-modules-js/javascript/minify-vendor.es6.js');
const watch = require('./build-modules-js/watch.es6.js');
const { gzipFiles } = require('./build-modules-js/gzip-assets.es6');

// The settings
const options = require('../package.json');
const settings = require('./build-modules-js/settings.json');

// Merge Joomla's specific settings to the main package.json object
if ('settings' in settings) {
  options.settings = settings.settings;
}

// Initialize the CLI
Program
  .version(options.version)
  .option('--copy-assets', 'Moving files from node_modules to media folder')
  .option('--build-pages', 'Creates the error pages for unsupported PHP version & incomplete environment')
  .option('--compile-js, --compile-js path', 'Handles ES6, ES5 and web component scripts')
  .option('--compile-css, --compile-css path', 'Compiles all the scss files to css')
  .option('--watch', 'Watch file changes and re-compile (ATM only works for the js in the media_source).')
  .option('--gzip', 'Compress all the minified stylesheets and scripts.')
  .on('--help', () => {
    // eslint-disable-next-line no-console
    console.log(`Version: ${options.version}`);
    process.exit(0);
  })
  .parse(process.argv);


// Show help by default
if (!process.argv.slice(2).length) {
  Program.outputHelp();
  process.exit(1);
}

// Update the vendor folder
if (Program.copyAssets) {
  Promise.resolve()
    .then(init.copyAssets(options))
    .then(minifyVendor.compile(options))

    // Exit with success
    .then(() => process.exit(0))

    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(err);
      process.exit(-1);
    });
}


// Creates the error pages for unsupported PHP version & incomplete environment
if (Program.buildPages) {
  Promise.resolve()
    .then(() => {
      errorPages.run(options);
      })
    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(err);
      process.exit(-1);
    });
}

// Convert scss to css
if (Program.compileCss) {
  compileCSS.compile(options, Program.args[0]);
}

// Compress/transpile the javascript files
if (Program.compileJs) {
  compileJS.compileJS(options, Program.args[0]);
}

// Compress/transpile the javascript files
if (Program.watch) {
  watch.run();
}

// Gzip js/css files
if (Program.gzip) {
  gzipFiles();
}
