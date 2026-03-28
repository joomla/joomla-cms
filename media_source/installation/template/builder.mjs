/**
 * Assets Builder
 */
import path from 'node:path';
import fsp from "node:fs/promises";
import fs from "node:fs";

import DefaultModuleBuilder from '../../../build/build-modules-js/builder/default-module-builder.mjs';


export default class InstallationTmplModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Class constructor.
   *
   * @param { string } name        The folder (builder) name. Relative to media/.
   * @param { string } basePath    Base path to the media source, without builder name.
   * @param { string } targetPath  Path to the media target, without builder name.
   * @param { object } options     Options object, from cmd or etc.
   */
  constructor(name = '', basePath = '', targetPath = '', options = {}) {
    super(name, basePath, targetPath, options);

    // Update target, because it is outside /media folder
    this.targetPath = path.join(path.dirname(targetPath), name);
  }

  /**
   * Remove files on target location
   * @returns { Promise }
   */
  async clear() {
    if (!fs.existsSync(this.targetPath)) {
      return;
    }
    const promises = [];

    // Do not remove all, since the target contain PHP files also
    ['css', 'js', 'images', 'scss'].forEach((dir) => {
      const fullPath = path.join(this.targetPath, dir);

      if (!fs.existsSync(fullPath)) {
        return;
      }

      promises.push(fsp.rm(fullPath, { recursive: true }));
    });

    return Promise.all(promises);
  }
};
