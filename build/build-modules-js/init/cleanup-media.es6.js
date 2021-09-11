const {
  stat, mkdir, copy, remove,
} = require('fs-extra');
const { join } = require('path');

const RootPath = process.cwd();

/**
 * Method that will erase the media/vendor folder
 * and populate the debugbar assets
 *
 * @returns {Promise}
 */
module.exports.cleanVendors = async (skip = true) => {
  // eslint-disable-next-line no-console
  console.log('Cleanup the Vendor ');

  try {
    const mediaFolder = await stat(join(RootPath, 'libraries/vendor/maximebf/debugbar/src/DebugBar/Resources'));
    await mediaFolder.isDirectory();
  } catch (e) {
    // eslint-disable-next-line no-console
    console.error('You need to run `npm install` AFTER the command `composer install`!!!. The debug plugin HASN\'T installed all its front end assets');
    return;
  }

  // Recreate the media folder
  await mkdir(join(RootPath, 'media/vendor/debugbar'), { recursive: true, mode: 0o755 });

  // Copy some assets from a PHP package
  await copy(join(RootPath, 'libraries/vendor/maximebf/debugbar/src/DebugBar/Resources'), join(RootPath, 'media/vendor/debugbar'), { preserveTimestamps: true });
  await remove(join(RootPath, 'media/vendor/debugbar/vendor/font-awesome'));
  await remove(join(RootPath, 'media/vendor/debugbar/vendor/jquery'));
};
