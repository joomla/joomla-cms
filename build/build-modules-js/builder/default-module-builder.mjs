/**
 * Default Assets Builder
 */

import path from 'node:path';
import fs from 'node:fs';
import fse from 'fs-extra/esm'

export default class DefaultModuleBuilder{
  /**
   * List of task the builder can run.
   * Basically list of the class methods that allowed to be called from CLI.
   *
   * @type {string[]}
   */
  tasks = ['clear', 'copy', 'css', 'js'];

  constructor(name = '', basePath = '', targetPath = '') {
    this.name = name;
    this.basePath = basePath;
    this.targetPath = targetPath;

    if (!name) {
      throw new Error(`Argument "name" is required for ModuleBuilder.`);
    }

    if (!basePath || !targetPath) {
      throw new Error(`Arguments "basePath" and "targetPath"  is required for "${name}" ModuleBuilder.`);
    }

    // Internal flag
    this.copyDone = false;
  }

  getTasks() {
    return this.tasks;
  }

  /**
   * Remove files on target location
   * @returns {Promise<void>}
   */
  async clear() {
    // return fse.remove(this.targetPath);
  }

  /**
   * Copy files to target location
   * @returns {Promise<void>}
   */
  async copy() {

    const filterFunc = (src, dest) => {
      if (dest === this.targetPath) {
        return true;
      }

      // Skip build module
      if (path.basename(src) === 'build.mjs') {
        return false;
      }

      // Skip folders for child modules
      if (fs.existsSync(path.join(src, 'build.mjs'))) {
        return false;
      }

      return true;
    };

    return fse.copy(this.basePath, this.targetPath, { filter: filterFunc }).then(() => {
      this.copyDone = true;
    });
  }

  /**
   * Process CSS files
   * @returns {Promise<void>}
   */
  async css() {
    // Make sure files is copied
    if (!this.copyDone) {
      return this.copy().then(() => this.css());
    }

    //console.log('Building css task ...');
  }

  /**
   * Process JavaScript files and Modules
   * @returns {Promise<void>}
   */
  async js() {
    // Make sure files is copied
    if (!this.copyDone) {
      return this.copy().then(() => this.js());
    }

    // @TODO Task "js"
    //console.log('Building js task ...');
  }
}
