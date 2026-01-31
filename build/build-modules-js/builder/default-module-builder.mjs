/**
 * Default Assets Builder
 */

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
  }

  getTasks() {
    return this.tasks;
  }

  /**
   * Remove files on target location
   * @returns {Promise<void>}
   */
  async clear() {
    return fse.remove(this.targetPath);
  }

  /**
   * Copy files to target location
   * @returns {Promise<void>}
   */
  async copy() {
    // @TODO Task "copy"
    console.log('Building copy task ...');

    return fse.mkdirp(this.targetPath);
  }

  /**
   * Process CSS files
   * @returns {Promise<void>}
   */
  async css() {
    // @TODO Task "css"
    console.log('Building css task ...');
  }

  /**
   * Process JavaScript files and Modules
   * @returns {Promise<void>}
   */
  async js() {
    // @TODO Task "js"
    console.log('Building js task ...');
  }
}
