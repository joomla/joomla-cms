import { join, sep } from 'node:path';
import { stat } from 'node:fs/promises';
import { readdirSync } from 'node:fs';

import { handleScssFile } from './stylesheets/handle-scss.mjs';
import { handleCssFile } from './stylesheets/handle-css.mjs';

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
 * @param {string|Array} path     The folder that needs to be compiled, optional
 *
 * @returns {Promise}
 */
export const stylesheets = async (path) => {
  let files = [];
  let folders = [];

  if (Array.isArray(path)) {
    path.filter((file) => file.endsWith('.css') || file.endsWith('.scss')).forEach((file) => files.push(file));
  } else if (path) {
    const stats = await stat(`${RootPath}/${path}`);

    if (stats.isDirectory()) {
      folders.push(`${RootPath}/${path}`);
    } else if (stats.isFile()) {
      files.push(`${RootPath}/${path}`);
    } else {
      throw new Error(`Unknown path ${path}`);
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

  if (folders.length) {
    // Loop to get the files that should be compiled via parameter
    for (const folder of folders) {
      const filesLocal = readdirSync(folder, { recursive: true })
        .filter((file) => file.endsWith('.scss') || file.endsWith('.css'))
        .map((file) => join(folder, file));
        for (const file of filesLocal) {
          files.push(file);
        }
    }
  }

  const cssFilesPromises = [];
  const scssFilesPromises = [];

  // Loop to get the files that should be compiled via parameter
  files.forEach((file) => {
    if (file.endsWith('.css') && !file.endsWith('.min.css')) {
      cssFilesPromises.push(handleCssFile(file));
    }

    // Don't take files with "_" but "file" has the full path, so check via match
    if (file.endsWith('.scss') && !file.match(/(\/|\\)_[^/\\]+$/)) {
      // Bail out for non Joomla convention folders, eg: scss
      if (!(file.match(/\/scss\//) || file.match(/\\scss\\/))) {
        return;
      }

      const outputFile = file.replace(`${sep}scss${sep}`, `${sep}css${sep}`)
        .replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`)
        .replace('.scss', '.css');

      scssFilesPromises.push(handleScssFile(file, outputFile));
    }
  });

  return Promise.all([...cssFilesPromises, ...scssFilesPromises]).catch((err) => { throw new Error(err); });
};
