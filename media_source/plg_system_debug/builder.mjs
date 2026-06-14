/**
 * Assets Builder
 */
import path from 'node:path';
import fsp from "node:fs/promises";
import fs from "node:fs";
import DefaultModuleBuilder from '../../build/build-modules-js/builder/default-module-builder.mjs';

export default class PlgDebugModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Target location for vendor files of the debugbar
   * @type {string}
   */
  vendorLocation = 'vendor/debugbar';

  /**
   * Remove files
   * @returns { Promise }
   */
  async clear() {
    await super.clear();

    const pathMedia = path.dirname(this.targetPath);
    const pathVendor = path.join(pathMedia, this.vendorLocation);

    if (!fs.existsSync(pathVendor)) {
      return;
    }

    return fsp.rm(pathVendor, { recursive: true });
  }

  /**
   * Copy files to target location.
   *
   * @returns { Promise }
   */
  async copy() {
    await super.copy();

    // Copy DebugBar assets to vendor folder
    const pathSrc = 'libraries/vendor/php-debugbar/php-debugbar/src/DebugBar/Resources';
    const pathMedia = path.dirname(this.targetPath);
    const pathVendor = path.join(pathMedia, this.vendorLocation);

    if (!fs.existsSync(pathSrc)) {
      throw new Error('DebugBar/Resources not found. Make sure you have run "composer install" first.')
    }

    return fsp.cp(pathSrc, pathVendor, { recursive: true, preserveTimestamps: true }).then(() => {
      // Remove unused
      return Promise.all([
        fsp.rm(path.join(pathVendor, 'vendor/font-awesome'), { recursive: true }),
        fsp.rm(path.join(pathVendor, 'vendor/jquery'), { recursive: true }),
      ]);
    });
  }

  /**
   * Process CSS files
   * @returns { Promise }
   */
  async css() {
    await super.css();

    // Run default css builder for vendor files
    const pathMedia = path.dirname(this.targetPath);
    const builder = new DefaultModuleBuilder(
      this.vendorLocation,
      pathMedia,
      pathMedia,
      this.options
    );

    return builder.css();
  }

  /**
   * Process JavaScript files and Modules
   * @returns { Promise }
   */
  async js() {
    await super.js();

    // Run default js builder for vendor files
    const pathMedia = path.dirname(this.targetPath);
    const builder = new DefaultModuleBuilder(
      this.vendorLocation,
      pathMedia,
      pathMedia,
      this.options
    );

    return builder.js();
  }
};
