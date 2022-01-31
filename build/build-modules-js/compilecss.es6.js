const { stat } = require('fs-extra');
const { sep } = require('path');
const recursive = require('recursive-readdir');
const { handleScssFile } = require('./stylesheets/handle-scss.es6.js');
const { handleCssFile } = require('./stylesheets/handle-css.es6.js');

const RootPath = process.cwd();

/**
 * Method that will crawl the media_source folder
 * and compile any scss files to css and .min.css
 * copy any css files to the appropriate destination and
 * minify them in place
 *
 * Expects scss files to have ext: .scss
 *         css files to have ext: .css
 * Ignores scss files that their filename starts with `_`
 *
 * @param {object} options  The options
 * @param {string} path     The folder that needs to be compiled, optional
 */
module.exports.stylesheets = async (options, path) => {
  const files = [];
  let folders = [];

  if (path) {
    const stats = await stat(`${RootPath}/${path}`);

    if (stats.isDirectory()) {
      folders.push(`${RootPath}/${path}`);
    } else if (stats.isFile()) {
      files.push(`${RootPath}/${path}`);
    } else {
      // eslint-disable-next-line no-console
      console.error(`Unknown path ${path}`);
      process.exit(1);
    }
  } else {
    folders = [
      `${RootPath}/build/media_source`,
      `${RootPath}/templates`,
      `${RootPath}/administrator/templates`,
      `${RootPath}/installation/template`,
      `${RootPath}/media/vendor/debugbar`,
    ];
  }

  const folderPromises = [];

  // Loop to get the files that should be compiled via parameter
  // eslint-disable-next-line no-restricted-syntax
  for (const folder of folders) {
    folderPromises.push(recursive(folder, ['!*.+(scss|css)']));
  }

  const computedFiles = await Promise.all(folderPromises);

  const cssFilesPromises = [];
  const scssFilesPromises = [];

  // Loop to get the files that should be compiled via parameter
  [].concat(...computedFiles).forEach((file) => {
    if (file.endsWith('.css') && !file.endsWith('.min.css')) {
      cssFilesPromises.push(handleCssFile(file));
    }

    // Don't take files with "_" but "file" has the full path, so check via match
    if (file.endsWith('.scss') && !file.match(/(\/|\\)_[^/\\]+$/)) {
      // Bail out for non Joomla convention folders, eg: scss
      if (!(file.match(/\/scss\//) || file.match(/\\scss\\/))) {
        return;
      }

      files.push(file);
    }
  });

  // eslint-disable-next-line no-restricted-syntax
  for (const file of files) {
    const outputFile = file.replace(`${sep}scss${sep}`, `${sep}css${sep}`)
      .replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`)
      .replace('.scss', '.css');

    scssFilesPromises.push(handleScssFile(file, outputFile));
  }

  return Promise.all([...cssFilesPromises, ...scssFilesPromises]);
};
