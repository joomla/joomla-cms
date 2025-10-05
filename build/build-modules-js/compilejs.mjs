import { stat } from 'node:fs/promises';
import { join, sep } from 'node:path';
import { readdirSync } from 'node:fs';
import { handleES5File } from './javascript/handle-es5.mjs';
import { handleESMFile } from './javascript/compile-to-es2017.mjs';

const RootPath = process.cwd();

/**
 * Method that will crawl the media_source folder and
 * compile ES6 to ES5 and ES6
 * copy any ES5 files to the appropriate destination and
 * minify them in place
 * compile any custom elements/webcomponents
 *
 * Expects ES6 files to have ext: .es6.js
 *         ES5 files to have ext: .es5.js
 *         WC/CE files to have ext: .w-c.es6.js
 *
 * @param {string|Array} path    The folder that needs to be compiled, optional
 *
 * @returns {Promise}
 */
export const scripts = async (path) => {
  let files = [];
  let folders = [];

  if (Array.isArray(path)) {
    path.filter((file) => file.endsWith('.mjs') || file.endsWith('.js')).forEach((file) => files.push(file));
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
      `${RootPath}/templates/cassiopeia`,
    ];
  }

  if (folders.length) {
    // Loop to get the files that should be compiled via parameter
    for (const folder of folders) {
      const filesLocal = readdirSync(folder, { recursive: true })
        .filter((file) => file.endsWith('.mjs') || file.endsWith('.js'))
        .map((fileName) => join(folder, fileName))
        for (const file of filesLocal) {
          files.push(file);
        }
    }
  }

  const jsFilesPromises = [];
  const esmFilesPromises = [];

  // Loop to get the files that should be compiled via parameter
  files.forEach((file) => {
    if (file.includes(`build${sep}media_source${sep}vendor${sep}bootstrap${sep}js`)) {
      return;
    }

    if (file.endsWith('.es5.js')) {
      jsFilesPromises.push(handleES5File(file));
    } else if ((file.endsWith('.es6.js') || file.endsWith('.w-c.es6.js')) && !file.startsWith('_')) {
      esmFilesPromises.push(handleESMFile(file));
    }
  });

  return Promise.all([...jsFilesPromises, ...esmFilesPromises]).catch((err) => { throw new Error(err); });
};
