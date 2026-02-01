/**
 * Default Assets Builder
 */

import fs from 'node:fs';
import fse from 'fs-extra/esm'
import fsp from 'node:fs/promises';
import path, { sep } from 'node:path';
import { handleCSS } from '../stylesheets/css-handler.mjs';
import { handleSCSS } from '../stylesheets/scss-handler.mjs';

export default class DefaultModuleBuilder{
  /**
   * List of task the builder can run.
   * Basically list of the class methods that allowed to be called from CLI.
   *
   * @type {string[]}
   */
  tasks = ['clear', 'copy', 'css', 'js'];

  constructor(name = '', basePath = '', targetPath = '') {
    if (!name) {
      throw new Error(`Argument "name" is required for ModuleBuilder.`);
    }

    if (!basePath || !targetPath) {
      throw new Error(`Arguments "basePath" and "targetPath"  is required for "${name}" ModuleBuilder.`);
    }

    this.name = name;
    this.basePath = path.join(basePath, name);
    this.targetPath = path.join(targetPath, name);

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
    return fse.remove(this.targetPath);
  }

  /**
   * Copy files to target location.
   * Skip:
   *  - css and js files
   *  - build.mjs and src/ and folders contain builder.mjs or .buildignore
   *
   * @returns {Promise<void>}
   */
  async copy() {
    const ignoreName = {
      'builder.mjs': true,
      'src': true,
      '.buildignore': true,
    };
    const ignoreExt = {
      '.js': true,
      '.css': true,
    }

    const filterFunc = (src, dest) => {
      if (dest === this.targetPath) {
        return true;
      }

      const baseName = path.basename(src);
      const fileStat = fs.statSync(src);

      // Skip ignored files/folders
      if (ignoreName[baseName]) {
        return false;
      }

      // Skip files with extensions
      if (fileStat.isFile() && ignoreExt[path.extname(baseName)]){
        return false;
      }

      // Skip folders for child modules or explicitly ignored
      if (fs.existsSync(path.join(src, 'builder.mjs')) || fs.existsSync(path.join(src, '.buildignore'))) {
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
   * @returns { Promise }
   */
  async css() {
    // Make sure files is copied
    if (!this.copyDone) {
      return this.copy().then(() => this.css());
    }

    // Collect files
    const cssFiles = [];
    const scssFiles = [];

    return fsp.readdir(this.basePath, { recursive: true, withFileTypes: true })
      .then((files) => {
        // Filter the files
        files.forEach((file) => {
          if (!file.isFile()) return;

          const baseName = file.name;
          const ext = path.extname(baseName);
          const fullSrcPath = path.join(file.path, file.name);
          const relativePath = fullSrcPath.replace(this.basePath, '');

          if (ext === '.css' && !baseName.endsWith('.min.css')){
            cssFiles.push(handleCSS(
              fullSrcPath,
              path.join(this.targetPath, relativePath))
            );
          } else if (ext === '.scss' && baseName[0] !== '_') {
            scssFiles.push(handleSCSS(
              fullSrcPath,
              path.join(this.targetPath, relativePath.replace(`${sep}scss${sep}`, `${sep}css${sep}`).replace('.scss', '.css')),
            ));
          }
        });

        return Promise.all([...cssFiles, ...scssFiles]);
      });
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
