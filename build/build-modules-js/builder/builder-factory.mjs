/**
 * Builder factory class
 */
import path from 'node:path';
import fs from 'node:fs';
import DefaultModuleBuilder from './default-module-builder.mjs';

//class BuilderRunner {]

export class BuilderFactory{
  constructor(basePath = '', targetPath = '') {
    this.basePath = basePath;
    this.targetPath = targetPath;
  }

  async createBuilder(name) {
    // Module base path
    const modBasePath = path.join(this.basePath, name);
    const modTargetPath = path.join(this.targetPath, name);
    let modulePath = path.join(modBasePath, 'builder.mjs');

    // Check if we have builder module
    try {
      fs.statSync(modulePath);
    } catch (e) {
      // Use default module
      return new DefaultModuleBuilder(name, modBasePath, modTargetPath);
    }

    return import(modulePath).then((module) => {
      return new module.default(name, modBasePath, modTargetPath);
    });
  }
}
