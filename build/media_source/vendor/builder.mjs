/**
 * Assets Builder
 */
import DefaultModuleBuilder from '../../build-modules-js/builder/default-module-builder.mjs';
import path from 'node:path';
import fsp from "node:fs/promises";

export default class VendorModuleBuilder extends DefaultModuleBuilder
{
  /**
   * List of Joomla custom stuff in /vendor dirs
   * @returns { Promise<[]> }
   */
  getExcludedFolders () {
    return fsp.readdir(this.basePath, { recursive: false, withFileTypes: true })
      .then((files) => {
        const exclude = [];

        files.forEach((file) => {
          if (!file.isDirectory()) return;
          exclude.push(file.name);
        });

        return exclude;
      });
  }

  /**
   * Remove files on target location
   * @returns { Promise }
   */
  async clear() {
    // Remove all except Joomla custom
    return this.getExcludedFolders().then((exclude) => {
      // Lookup folders on target
      return fsp.readdir(this.targetPath, { recursive: false, withFileTypes: true })
        .then((files) => {
          const rms = [];
          files.forEach((file) => {
            if (exclude.includes(file.name)) return;

            rms.push(
              fsp.rm(path.join(file.path, file.name), { recursive: true })
            );
          });

          return Promise.all(rms);
        });
    });
  }
};
