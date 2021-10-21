/**
 * Command line helper
 *
 * To get the complete functional media folder please run:
 * npm ci
 *
 * For dedicated tasks, please run:
 * node build.js --build-pages      will create the error pages (for incomplete repo build PHP+NPM)
 * node build.js --copy-assets      will clean the media/vendor folder and then will populate the folder from node_modules
 * node build.js --compile-js       will transpile ES6 files and also uglify the ES6,ES5 files
 * node build.js --compile-css      will compile all the scss defined files and also create a minified version of the css
 * node build.js --compile-bs       will compile all the Bootstrap javascript components
 * node build.js --com-media        will compile the media manager Vue application
 * node build.js --watch-com-media  will watch and compile the media manager Vue application
 * node build.js --gzip             will create gzip files for all the minified stylesheets and scripts.
 * node build.js --versioning       will update all the joomla.assets.json files providing accurate versions for stylesheets and scripts.
 */

const { Command } = require('commander');
const semver = require('semver');

// Joomla Build modules
const { createErrorPages } = require('./build-modules-js/error-pages.es6.js');
const { stylesheets } = require('./build-modules-js/compilecss.es6.js');
const { scripts } = require('./build-modules-js/compilejs.es6.js');
const { bootstrapJs } = require('./build-modules-js/javascript/build-bootstrap-js.es6.js');
const { localisePackages } = require('./build-modules-js/init/localise-packages.es6.js');
const { minifyVendor } = require('./build-modules-js/init/minify-vendor.es6.js');
const { patchPackages } = require('./build-modules-js/init/patches.es6.js');
const { cleanVendors } = require('./build-modules-js/init/cleanup-media.es6.js');
const { recreateMediaFolder } = require('./build-modules-js/init/recreate-media.es6');
const { watching } = require('./build-modules-js/watch.es6.js');
const { mediaManager, watchMediaManager } = require('./build-modules-js/javascript/build-com_media-js.es6');
const { compressFiles } = require('./build-modules-js/compress.es6.js');
const { versioning } = require('./build-modules-js/versioning.es6.js');
const { Timer } = require('./build-modules-js/utils/timer.es6.js');

// The settings
const options = require('../package.json');
const settings = require('./build-modules-js/settings.json');

// The command line
const Program = new Command();

// Merge Joomla's specific settings to the main package.json object
if ('settings' in settings) {
  options.settings = settings.settings;
}

const handleError = (err, terminateCode) => {
  // eslint-disable-next-line no-console
  console.error(err);
  process.exit(terminateCode);
};

const allowedVersion = () => {
  if (!semver.satisfies(process.version.substring(1), options.engines.node)) {
    handleError(`Command line tools require Node Version ${options.engines.node} but found ${process.version}`, -1);
  }
};

// Initialize the CLI
Program
  .allowUnknownOption()
  .version(options.version)
  .option('--copy-assets', 'Moving files from node_modules to media folder')
  .option('--build-pages', 'Creates the error pages for unsupported PHP version & incomplete environment')
  .option('--compile-js, --compile-js path', 'Handles ES6, ES5 and web component scripts')
  .option('--compile-css, --compile-css path', 'Compiles all the scss files to css')
  .option('--compile-bs', 'Compiles all the Bootstrap component scripts.')
  .option('--watch', 'Watch file changes and re-compile (ATM only works for the js in the media_source).')
  .option('--com-media', 'Compile the Media Manager client side App.')
  .option('--watch-com-media', 'Watch and Compile the Media Manager client side App.')
  .option('--gzip', 'Compress all the minified stylesheets and scripts.')
  .option('--prepare', 'Run all the needed tasks to initialise the repo')
  .option('--versioning', 'Update all the .js/.css versions on their relative joomla.assets.json')

  .addHelpText('after', `
Version: ${options.version}
`);

Program.parse(process.argv);

const cliOptions = Program.opts();

// Update the vendor folder
if (cliOptions.copyAssets) {
  allowedVersion();
  recreateMediaFolder(options)
    .then(() => cleanVendors())
    .then(() => localisePackages(options))
    .then(() => patchPackages(options))
    .then(() => minifyVendor())
    .then(() => {
      process.exit(0);
    })
    .catch((error) => handleError(error, 1));
}

// Creates the error pages for unsupported PHP version & incomplete environment
if (cliOptions.buildPages) {
  createErrorPages(options)
    .catch((err) => handleError(err, 1));
}

// Convert scss to css
if (cliOptions.compileCss) {
  stylesheets(options, Program.args[0])
    .catch((err) => handleError(err, 1));
}

// Compress/transpile the javascript files
if (cliOptions.compileJs) {
  scripts(options, Program.args[0])
    .catch((err) => handleError(err, 1));
}

// Compress/transpile the javascript files
if (cliOptions.watch) {
  watching(Program.args[0]);
}

// Gzip js/css files
if (cliOptions.compileBs) {
  bootstrapJs();
}

// Gzip js/css files
if (cliOptions.gzip) {
  compressFiles();
}

// Compile the media manager
if (cliOptions.comMedia) {
  // false indicates "no watch"
  mediaManager(false);
}

// Watch & Compile the media manager
if (cliOptions.watchComMedia) {
  watchMediaManager(true);
}

// Update the .js/.css versions
if (cliOptions.versioning) {
  versioning()
    .catch((err) => handleError(err, 1));
}

// Prepare the repo for dev work
if (cliOptions.prepare) {
  const bench = new Timer('Build');
  allowedVersion();
  recreateMediaFolder(options)
    .then(() => cleanVendors())
    .then(() => localisePackages(options))
    .then(() => patchPackages(options))
    .then(() => Promise.all(
      [
        minifyVendor(),
        createErrorPages(options),
        stylesheets(options, Program.args[0]),
        scripts(options, Program.args[0]),
        bootstrapJs(),
        mediaManager(true),
      ],
    ))
    .then(() => bench.stop('Build'))
    .then(() => { process.exit(0); })
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(err);
      process.exit(-1);
    });
}
