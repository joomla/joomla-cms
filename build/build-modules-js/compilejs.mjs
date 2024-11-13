import { stat } from 'node:fs/promises';
import { sep } from 'node:path';

import recursive from 'recursive-readdir';

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
 * @param { object } options The options from settings.json
 * @param { string } path    The folder that needs to be compiled, optional
 * @param { string } mode    esm for ES2017, es5 for ES5, both for both
 */
export const scripts = async (options, path) => {
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
      process.exitCode = 1;
    }
  } else {
    folders = [
      `${RootPath}/build/media_source`,
      `${RootPath}/templates/cassiopeia`,
    ];
  }

  const folderPromises = [];

  // Loop to get the files that should be compiled via parameter
  // eslint-disable-next-line no-restricted-syntax
  for (const folder of folders) {
    folderPromises.push(recursive(folder, ['!*.+(m|js)']));
  }

  const computedFiles = await Promise.all(folderPromises);
  const computedFilesFlat = [].concat(...computedFiles);
  const jsFilesPromises = [];
  const esmFilesPromises = [];

  // Loop to get the files that should be compiled via parameter
  computedFilesFlat.forEach((file) => {
    if (file.includes(`build${sep}media_source${sep}vendor${sep}bootstrap${sep}js`)) {
      return;
    }

    if (file.endsWith('.es5.js')) {
      jsFilesPromises.push(handleES5File(file));
    } else if ((file.endsWith('.es6.js') || file.endsWith('.w-c.es6.js')) && !file.startsWith('_')) {
      esmFilesPromises.push(handleESMFile(file));
    }
  });

  Promise.all([...jsFilesPromises, ...esmFilesPromises]);
};
