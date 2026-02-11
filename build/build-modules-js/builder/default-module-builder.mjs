/**
 * Default Assets Builder
 */

import fs from 'node:fs';
import fsp from 'node:fs/promises';
import path, { sep } from 'node:path';
import { handleCSSFile } from '../stylesheets/css-handler.mjs';
import { handleSCSSFile } from '../stylesheets/scss-handler.mjs';
import { handleMJSFile, handleJSFile } from "../javascript/js-handle.mjs";
import { compressFileAndSave } from '../utils/compressFile.mjs';

export default class DefaultModuleBuilder{

  /**
   * List of tasks to be executed by default for the build process.
   * Basically list of the class methods that allowed to be called from CLI.
   *
   * @type {string[]}
   */
  tasksBuild = ['clear', 'copy', 'css', 'js'];

  /**
   * List of extra tasks that the builder can run, but which is not executed during the build process.
   *
   * @type {string[]}
   */
  tasksExtras = ['gzip'];

  /**
   * Class constructor.
   *
   * @param { string } name        The folder (builder) name. Relative to media/.
   * @param { string } basePath    Base path to the media source, without builder name.
   * @param { string } targetPath  Path to the media target, without builder name.
   * @param { object } options     Options object, from cmd or etc.
   */
  constructor(name = '', basePath = '', targetPath = '', options = {}) {
    if (!name) {
      throw new Error(`Argument "name" is required for ModuleBuilder.`);
    }

    if (!basePath || !targetPath) {
      throw new Error(`Arguments "basePath" and "targetPath"  is required for "${name}" ModuleBuilder.`);
    }

    this.name = name;
    this.basePath = path.join(basePath, name);
    this.targetPath = path.join(targetPath, name);
    this.options = options;
  }

  getAllTasks() {
    return [ ...this.tasksBuild, ...this.tasksExtras ];
  }

  getBuildTasks() {
    return this.tasksBuild;
  }

  /**
   * Remove files on target location
   * @returns { Promise }
   */
  async clear() {
    if (!fs.existsSync(this.targetPath)) {
      return;
    }

    return fsp.rm(this.targetPath, { recursive: true });
  }

  /**
   * Copy files to target location.
   * Skip:
   *  - css and js files
   *  - build.mjs and src/ and folders contain builder.mjs or .buildignore
   *
   * @returns { Promise }
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

    return fsp.cp(this.basePath, this.targetPath, { filter: filterFunc, recursive: true });
  }

  /**
   * Process CSS files
   * @returns { Promise }
   */
  async css() {
    // Collect files
    const cssFiles = [];
    const scssFiles = [];

    return fsp.readdir(this.basePath, { recursive: true, withFileTypes: true })
      .then((files) => {
        // Filter and handle the files
        files.forEach((file) => {
          if (!file.isFile()) return;

          const baseName = file.name;
          const ext = path.extname(baseName);
          const fullSrcPath = path.join(file.path, file.name);
          const relativePath = fullSrcPath.replace(this.basePath, '');

          if (ext === '.css' && !baseName.endsWith('.min.css')){
            // Handle the CSS file
            cssFiles.push(handleCSSFile(
              fullSrcPath,
              path.join(this.targetPath, relativePath))
            );
          } else if (ext === '.scss' && baseName[0] !== '_') {
            // Handle the SCSS file
            scssFiles.push(handleSCSSFile(
              fullSrcPath,
              path.join(this.targetPath, relativePath.replace(`${sep}scss${sep}`, `${sep}css${sep}`).replace('.scss', '.css')),
              this.options.sassSilent
            ));
          }
        });

        return Promise.all([...cssFiles, ...scssFiles]);
      });
  }

  /**
   * Process JavaScript files and Modules
   * @returns { Promise }
   */
  async js() {
    // Collect files
    const jsFiles = [];
    const mjsFiles = [];

    return fsp.readdir(this.basePath, { recursive: true, withFileTypes: true })
      .then((files) => {
        // Filter and handle the files
        files.forEach((file) => {
          if (!file.isFile()) return;

          const baseName = file.name;
          const ext = path.extname(baseName);
          const fullSrcPath = path.join(file.path, file.name);
          const relativePath = fullSrcPath.replace(this.basePath, '');

          if ((ext !== '.js' && ext !== '.mjs') || baseName.endsWith('.min.js')) return;


          if (baseName === 'builder.mjs') {
            // Ignore builders of active asset, which is in the root of the asset folder
            if (relativePath.replace('/', '').replace('\\', '') === 'builder.mjs') {
              return;
            }

            // This should never happen
            throw new Error(`Trying to build script from another builder "${fullSrcPath}". Make sure that each builder is separated.`);
          }

          if ((ext === '.mjs' || baseName.endsWith('.es6.js') || baseName.endsWith('.w-c.es6.js')) && !baseName.startsWith('_')) {
            mjsFiles.push(handleMJSFile(
              fullSrcPath,
              path.join(this.targetPath, relativePath.replace(/\.w-c\.es6\.js$/, '.js').replace(/\.es6\.js$/, '.js'))
            ));
          } else {
            jsFiles.push(handleJSFile(
              fullSrcPath,
              path.join(this.targetPath, relativePath)
            ));
          }
        });

        return Promise.all([...jsFiles, ...mjsFiles]);
      });
  }

  /**
   * Create compressed (gzip) .css/.js files
   * @returns { Promise }
   */
  async gzip() {
    return fsp.readdir(this.targetPath, { recursive: true, withFileTypes: true }).then((files) => {
      const promises = [];

      files.forEach((file) => {
        if (!file.isFile()) return;

        const fullSrcPath = path.join(file.path, file.name);

        // Compress only minified
        if (!file.name.endsWith('.min.js') && !file.name.endsWith('.min.css')) return;

        promises.push(compressFileAndSave(fullSrcPath));
      });

      return Promise.all(promises);
    });
  }
}
