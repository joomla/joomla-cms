/**
 * Command line helper
 *
 * To get the complete functional media folder please run
 *
 * npm install
 *
 * For dedicated tasks, please run:
 * node build.js --buildcheck     === will create the error page (for incomplete repo build)
 * node build.js --installer       === will create the error page (for unsupported PHP version)
 * node build.js --copy-assets     === will clean the media/vendor folder and then will populate the folder from node_modules
 * node build.js --compile-js      === will transpile ES6 files and also uglify the ES6,ES5 files
 * node build.js --compile-ce      === will compile all the given CE or WC with their relative css files
 * node build.js --compile-css     === will compile all the scss defined files and also create a minified version of the css
 *
 */

// eslint-disable-next-line import/no-extraneous-dependencies
const Program = require('commander');
// eslint-disable-next-line import/no-extraneous-dependencies

// Joomla Build modules
const buildCheck = require('./build/build-modules-js/build-check');
const update = require('./build/build-modules-js/update');
const css = require('./build/build-modules-js/compilescss');
const Js = require('./build/build-modules-js/compilejs');
const CEjs = require('./build/build-modules-js/compilecejs');
const fixVend = require('./build/build-modules-js/minify-vendor');

// The settings
const options = require('./package.json');
const settings = require('./build/build-modules-js/settings.json');

// Merge Joomla's specific settings to the main package.json object
if ('settings' in settings) {
  options.settings = settings.settings;
}

// Initialize the CLI
Program
  .version(options.version)
  .option('--copy-assets', 'Moving files from node_modules to media folder')
  .option('--compile-js, --compile-js path', 'Compiles ES6 to ES5 scripts')
  .option('--compile-css, --compile-css path', 'Compiles all the scss files to css')
  .option('--compile-ce, --compile-ce path', 'Compiles/traspiles all the custom elements files')
  .option('--watch, --watch path', 'Watch file changes and re-compile (Only work for compile-css and compile-js now).')
  .option('--build-check', 'Creates the error pages for unsupported PHP version & incomplete environment')
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
    .then(update.update(options))
    .then(fixVend.compile(options))

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
if (Program.buildCheck) {
    buildCheck.buildCheck(options);
}

// Convert scss to css
if (Program.compileCss) {
  if (Program.watch) {
    css.watch(options, null, true);
  } else {
    css.compile(options, Program.args[0]);
  }
}

// Compress/transpile the javascript files
if (Program.compileJs) {
  if (Program.watch) {
    Js.watch(options, null, false);
  } else {
    Js.compile(options, Program.args[0]);
  }
}

// Compress/transpile the Custom Elements files
if (Program.compileCe) {
  CEjs.compile(options, Program.args[0]);
}
