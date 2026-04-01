/**
 * Assets Builder
 */
import fs from "node:fs";
import fsp from "node:fs/promises";
import path from "node:path";

import DefaultModuleBuilder from '../../../build/build-modules-js/builder/default-module-builder.mjs';

export default class ShortAndSweetModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Remove files on target location
   * @returns { Promise }
   */
  async clear() {
    // Remove only custom files from this builder and leave vendor files
    const jsPath = path.join(this.targetPath, 'js');

    if (!fs.existsSync(jsPath)) {
      return;
    }

    return fsp.rm(jsPath, { recursive: true });
  }
};
