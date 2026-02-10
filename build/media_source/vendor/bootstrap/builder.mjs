/**
 * Assets Builder
 */
import fs from 'node:fs';
import fsp from 'node:fs/promises';
import path from 'node:path';
import DefaultModuleBuilder from '../../../build-modules-js/builder/default-module-builder.mjs';



export default class BootstrapModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Remove files on target location
   * @returns { Promise }
   */
  async clear() {
    // Do not clear whole target because it is mix of customized JS and source files from the bootstrap package.
    // Remove only related JS
    const jsPath = path.join(this.targetPath, 'js');

    if (!fs.existsSync(jsPath)) {
      return;
    }

    return fsp.rm(jsPath, { recursive: true });
  }

  /**
   * Process CSS files
   * @returns { Promise }
   */
  async css() {
    // Nothing to do here
  }
};
