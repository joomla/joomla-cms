import { join } from 'node:path';

import pkg from 'fs-extra';

const RootPath = process.cwd();
const {
  stat, mkdir, copy, remove,
} = pkg;

/**
 * Method that will erase the media/vendor folder
 * and populate the debugbar assets
 *
 * @returns {Promise}
 */
export const cleanVendors = async () => {
  if (process.env.SKIP_COMPOSER_CHECK === 'YES') {
    await mkdir('media/vendor/debugbar', { recursive: true, mode: 0o755 });
    // eslint-disable-next-line no-console
    console.log('Skipping the DebugBar assets...');
    return;
  }

  // eslint-disable-next-line no-console
  console.log('Cleanup the Vendor ');

  const mediaFolder = await stat(join(RootPath, 'libraries/vendor/maximebf/debugbar/src/DebugBar/Resources'));

  if (await mediaFolder.isDirectory()) {
    // Remove the vendor folder
    // await remove(join(RootPath, 'media'));
    // eslint-disable-next-line no-console
    // console.error('/media has been removed.');

    // Recreate the media folder
    await mkdir(join(RootPath, 'media/vendor/debugbar'), { recursive: true, mode: 0o755 });

    // Copy some assets from a PHP package
    await copy(join(RootPath, 'libraries/vendor/maximebf/debugbar/src/DebugBar/Resources'), join(RootPath, 'media/vendor/debugbar'), { preserveTimestamps: true });
    await remove(join(RootPath, 'media/vendor/debugbar/vendor/font-awesome'));
    await remove(join(RootPath, 'media/vendor/debugbar/vendor/jquery'));
  } else {
    // eslint-disable-next-line no-console
    console.error("You need to run `npm install` AFTER the command `composer install`!!!. The debug plugin HASN'T installed all its front end assets");
    process.exitCode = 1;
  }
};
