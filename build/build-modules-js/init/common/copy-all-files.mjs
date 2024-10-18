import { join } from 'node:path';

import { copy } from 'fs-extra';

const RootPath = process.cwd();

/**
 * Copies all the files from a directory
 *
 * @param {string} dirName the name of the source folder
 * @param {string} name    the name of the destination folder
 * @param {string} type    the type of the folder, eg: js, css, fonts, images
 *
 * @returns { void }
 */
export const copyAllFiles = async (dirName, name, type) => {
  const folderName = dirName === '/' ? '/' : `/${dirName}`;
  await copy(
    join(RootPath, `node_modules/${name}/${folderName}`),
    join(RootPath, `media/vendor/${name.replace(/.+\//, '')}/${type}`),
    {
      preserveTimestamps: true,
    },
  );
};
