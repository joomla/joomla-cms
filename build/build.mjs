/**
 * Command line helper
 *
 * To get the complete functional media folder please run:
 * npm ci
 *
 * For dedicated tasks, please run:
 * node build.mjs --build-pages      will create the error pages (for incomplete repo build PHP+NPM)
 * node build.mjs --copy-assets      will clean the media/vendor folder and then will populate the folder from node_modules
 * node build.mjs --compile-js       will transpile ES6 files and also uglify the ES6,ES5 files
 * node build.mjs --compile-css      will compile all the scss defined files and also create a minified version of the css
 * node build.mjs --compile-bs       will compile all the Bootstrap javascript components
 * node build.mjs --com-media        will compile the media manager Vue application
 * node build.mjs --watch-com-media  will watch and compile the media manager Vue application
 * node build.mjs --gzip             will create gzip files for all the minified stylesheets and scripts.
 * node build.mjs --cssversioning    will update all the url entries providing accurate versions for stylesheets.
 * node build.mjs --versioning       will update all the joomla.assets.json files providing accurate versions for stylesheets and scripts.
 */

import { createRequire } from 'node:module';
import { Command } from 'commander';
import semver from 'semver';

// Joomla Build modules
import { createErrorPages } from './build-modules-js/error-pages.mjs';
import { stylesheets } from './build-modules-js/compilecss.mjs';
import { scripts } from './build-modules-js/compilejs.mjs';
import { bootstrapJs } from './build-modules-js/javascript/build-bootstrap-js.mjs';
import { localisePackages } from './build-modules-js/init/localise-packages.mjs';
import { minifyVendor } from './build-modules-js/init/minify-vendor.mjs';
import { patchPackages } from './build-modules-js/init/patches.mjs';
import { cleanVendors } from './build-modules-js/init/cleanup-media.mjs';
import { recreateMediaFolder } from './build-modules-js/init/recreate-media.mjs';
import { watching } from './build-modules-js/watch.mjs';
import { mediaManager, watchMediaManager } from './build-modules-js/javascript/build-com_media-js.mjs';
import { compressFiles } from './build-modules-js/compress.mjs';
import { cssVersioning } from './build-modules-js/css-versioning.mjs';
import { versioning } from './build-modules-js/versioning.mjs';
import { Timer } from './build-modules-js/utils/timer.mjs';
import { compileCodemirror } from './build-modules-js/javascript/build-codemirror.mjs';

const require = createRequire(import.meta.url);

// The settings
const options = require('../package.json');
const settings = require('./build-modules-js/settings.json');

const handleError = (err, terminateCode) => {
  console.error(err); // eslint-disable-line no-console
  process.exitCode = terminateCode;
};

if (semver.gte(semver.minVersion(options.engines.node), semver.clean(process.version))) {
  handleError(
    `Node version ${semver.clean(process.version)} is not supported, please upgrade to Node version ${semver.clean(options.engines.node)}`,
    1,
  );
}

// The command line
const Program = new Command();

// Merge Joomla's specific settings to the main package.json object
if ('settings' in settings) {
  options.settings = settings.settings;
}

const allowedVersion = () => {
  if (!semver.satisfies(process.version.substring(1), options.engines.node)) {
    handleError(
      `Command line tools require Node Version ${options.engines.node} but found ${process.version}`,
      -1,
    );
  }
};

// Initialize the CLI
Program.allowUnknownOption()
  .version(options.version)
  .option('--copy-assets', 'Moving files from node_modules to media folder')
  .option(
    '--build-pages',
    'Creates the error pages for unsupported PHP version & incomplete environment',
  )
  .option(
    '--compile-js, --compile-js path',
    'Handles ES6, ES5 and web component scripts',
  )
  .option(
    '--compile-css, --compile-css path',
    'Compiles all the scss files to css',
  )
  .option('--compile-bs', 'Compiles all the Bootstrap component scripts.')
  .option('--compile-codemirror', 'Compiles all the codemirror modules.')
  .option(
    '--watch',
    'Watch file changes and re-compile (ATM only works for the js in the media_source).',
  )
  .option('--com-media', 'Compile the Media Manager client side App.')
  .option(
    '--watch-com-media',
    'Watch and Compile the Media Manager client side App.',
  )
  .option('--gzip', 'Compress all the minified stylesheets and scripts.')
  .option('--prepare', 'Run all the needed tasks to initialise the repo')
  .option(
    '--cssversioning',
    'Update all the url() versions on their relative stylesheet files',
  )
  .option(
    '--versioning',
    'Update all the .js/.css versions on their relative joomla.assets.json',
  )

  .addHelpText(
    'after',
    `
Version: ${options.version}
`,
  );

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
    .catch((error) => handleError(error, 1));
}

// Creates the error pages for unsupported PHP version & incomplete environment
if (cliOptions.buildPages) {
  createErrorPages(options).catch((err) => handleError(err, 1));
}

// Convert scss to css
if (cliOptions.compileCss) {
  stylesheets(options, Program.args[0]).catch((err) => handleError(err, 1));
}

// Compress/transpile the javascript files
if (cliOptions.compileJs) {
  scripts(options, Program.args[0]).catch((err) => handleError(err, 1));
}

// Compress/transpile the javascript files
if (cliOptions.watch) {
  watching(Program.args[0]);
}

// Gzip js/css files
if (cliOptions.compileBs) {
  bootstrapJs();
}

// Compile codemirror
if (cliOptions.compileCodemirror) {
  compileCodemirror();
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
  versioning().catch((err) => handleError(err, 1));
}

// Update the url() versions in the .css files
if (cliOptions.cssversioning) {
  cssVersioning().catch((err) => handleError(err, 1));
}

// Prepare the repo for dev work
if (cliOptions.prepare) {
  const bench = new Timer('Build');
  allowedVersion();
  recreateMediaFolder(options)
    .then(() => cleanVendors())
    .then(() => localisePackages(options))
    .then(() => patchPackages(options))
    .then(() => minifyVendor())
    .then(() => createErrorPages(options))
    .then(() => stylesheets(options, Program.args[0]))
    .then(() => scripts(options, Program.args[0]))
    .then(() => mediaManager())
    .then(() => bootstrapJs())
    .then(() => compileCodemirror())
    .then(() => bench.stop('Build'))
    .catch((err) => handleError(err, -1));
}
