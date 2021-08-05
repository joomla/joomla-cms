const { existsSync } = require('fs');
const { stat, copy, emptyDirSync } = require('fs-extra');
const { join, extname } = require('path');

const RootPath = process.cwd();

/**
 * Method to recreate the basic media folder structure
 * After execution the media folder is populated with empty js and css subdirectories
 * images subfolders with their relative files and any other files except .js, .css
 *
 * @returns {Promise}
 */
module.exports.recreateMediaFolder = async (options) => {
  // Clean up existing folders
  for (const folder of options.settings.cleanUpFolders) {
    const folderPath = join(`${RootPath}/media`, folder);
    if (existsSync(folderPath)) {
      emptyDirSync(folderPath);
    }
  }

  // eslint-disable-next-line no-console
  console.log('Recreating the media folder...');

  const filterFunc = async (src) => {
    const fileStat = await stat(src);
    if (fileStat.isFile() && (extname(src) === '.js' || extname(src) === '.css')) {
      return false;
    }

    return true;
  };

  await copy(join(RootPath, 'build/media_source'), join(RootPath, 'media'), { filter: filterFunc });
};
