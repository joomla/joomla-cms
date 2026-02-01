/**
 * Builder factory class
 */
import path from 'node:path';
import fs from 'node:fs';
import DefaultModuleBuilder from './default-module-builder.mjs';

//class BuilderRunner {}

export class BuilderFactory{
  constructor(basePath = '', targetPath = '') {
    this.basePath = basePath;
    this.targetPath = targetPath;
  }

  async createBuilder(name) {
    // Module path
    let modulePath = path.join(this.basePath, name, 'builder.mjs');

    // Check if we have the builder module
    if (!fs.existsSync(modulePath)) {
      // Use default module
      return new DefaultModuleBuilder(name, this.basePath, this.targetPath);
    }

    return import(modulePath).then((module) => {
      return new module.default(name, this.basePath, this.targetPath);
    });
  }
}
