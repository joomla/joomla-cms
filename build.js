/**
 * Command line helper
 *
 * To get the complete functional media folder please run
 *
 * npm install
 *
 * For dedicated tasks, please run:
 * node build.js --installer      === will create the error page (for unsupported PHP version)
 * node build.js --update         === will clean the media/vendor folder and then fetch the dependencies from source
 * node build.js --compilejs      === will transpile ES6 files and also uglify the ES6,ES5 files
 * node build.js --compilecejs    === will compile all the given CE or WC with their relative css files
 * node build.js --compilecss   === will compile all the scss defined files and also create a minified version of the css
 *
 */

// eslint-disable-next-line import/no-extraneous-dependencies
const Program = require('commander');
// eslint-disable-next-line import/no-extraneous-dependencies

// Joomla Build modules
const installer = require('./build/build-modules-js/installation.js');
const update = require('./build/build-modules-js/update.js');
const css = require('./build/build-modules-js/compilescss.js');
const Js = require('./build/build-modules-js/compilejs.js');
const CEjs = require('./build/build-modules-js/compilecejs.js');

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
  .option('--update', 'Updates the vendor scripts')
  .option('--compilejs, --compilejs path', 'Compiles ES6 to ES5 scripts')
  .option('--compilecss, --compilecss path', 'Compiles all the scss files to css')
  .option('--compilecejs, --compilecejs path', 'Compiles/traspiles all the custom elements files')
  .option('--watch, --watch path', 'Watch file changes and re-compile (Only work for compilecss and compilejs now).')
  .option('--installer', 'Creates the language file for installer error page')
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
if (Program.update) {
  Promise.resolve()
    .then(update.update(options))

    // Exit with success
    .then(() => process.exit(0))

    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(err);
      process.exit(-1);
    });
}

// Create the languages file for the error page on the installer
if (Program.installer) {
  installer.installation();
}

// Convert scss to css
if (Program.compilecss) {
  if (Program.watch) {
    css.watch(options, null, true);
  } else {
    css.compile(options, Program.args[0]);
  }
}

// Compress/transpile the javascript files
if (Program.compilejs) {
  if (Program.watch) {
    Js.watch(options, null, false);
  } else {
    Js.compile(options, Program.args[0]);
  }
}

// Compress/transpile the Custom Elements files
if (Program.compilececss) {
  CEcss.compile(options, Program.args[0]);
}

// Compress/transpile the Custom Elements files
if (Program.compilecejs) {
  CEjs.compile(options, Program.args[0]);
}
