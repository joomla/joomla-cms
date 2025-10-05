import { join } from 'node:path';
import { stat, mkdir, cp as copy, rm as remove } from 'node:fs/promises';

const RootPath = process.cwd();

/**
 * Method that will erase the media/vendor folder
 * and populate the debugbar assets
 *
 * @returns {Promise}
 */
export const cleanVendors = async () => {
  if (process.env.SKIP_COMPOSER_CHECK === 'YES') {
    await mkdir('media/vendor/debugbar', { recursive: true, mode: 0o755 });
    console.log('Skipping the DebugBar assets...');
    return;
  }

  console.log('Cleanup the Vendor ');

  const mediaFolder = await stat(join(RootPath, 'libraries/vendor/php-debugbar/php-debugbar/src/DebugBar/Resources'));

  if (await mediaFolder.isDirectory()) {
    // Remove the vendor folder
    // await remove(join(RootPath, 'media'));
    // console.error('/media has been removed.');

    // Recreate the media folder
    await mkdir(join(RootPath, 'media/vendor/debugbar'), { recursive: true, mode: 0o755 });

    // Copy some assets from a PHP package
    await copy(join(RootPath, 'libraries/vendor/php-debugbar/php-debugbar/src/DebugBar/Resources'), join(RootPath, 'media/vendor/debugbar'), { preserveTimestamps: true, recursive: true });
    await remove(join(RootPath, 'media/vendor/debugbar/vendor/font-awesome'), { recursive: true, force: true });
    await remove(join(RootPath, 'media/vendor/debugbar/vendor/jquery'), { recursive: true, force: true });
  } else {
    throw new Error("You need to run `npm install` AFTER the command `composer install`!!!. The debug plugin HASN'T installed all its front end assets");
  }
};
