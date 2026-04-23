/**
 * Assets Builder
 */

import path from 'node:path';
import fsp from "node:fs/promises";
import fs from "node:fs";
import DefaultModuleBuilder from '../../build/build-modules-js/builder/default-module-builder.mjs';
import { resolvePackageFile } from '../../build/build-modules-js/utils/resolve-package.mjs';


export default class TinyMCEModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Copy files to target location.
   *
   * @returns { Promise }
   */
  async copy() {
    await super.copy();

    const rootPath = process.cwd();
    const modulePathJson = resolvePackageFile(path.join('tinymce', 'package.json'));
    const moduleLngPathJson = resolvePackageFile(path.join('tinymce-i18n', 'package.json'));

    // This should never happen
    if (!modulePathJson || !moduleLngPathJson) {
      throw new Error(`Modules source for "tinymce" or for "tinymce-i18n" not found`);
    }

    const tinySrcPath = path.dirname(modulePathJson);
    const tinyVendorPath = path.join(path.dirname(this.targetPath), 'vendor', 'tinymce');
    const moduleOptions = await import(modulePathJson, { with: { type: 'json' } });
    const version = moduleOptions.default.version;
    const majorVersion = version.split('.')[0];
    const tinyLngSrcPath = path.join(path.dirname(moduleLngPathJson), `langs${majorVersion}`);

    // Copy vendor content
    const promises = [];
    const filterFunc = (src, dest) => {
      return path.basename(src) !== 'index.js';
    };

    [
      'icons',
      'plugins',
      'skins',
      'themes',
      'models',
      'tinymce.js',
      'tinymce.min.js',
      'CHANGELOG.md',
      'license.md',
    ].forEach((folder) => {
      promises.push(fsp.cp(
        path.join(tinySrcPath, folder),
        path.join(tinyVendorPath, folder),
        { preserveTimestamps: true, recursive: true, filter: filterFunc }
      ));
    });

    // Copy translation files
    if (fs.existsSync(tinyLngSrcPath)) {
      promises.push(fsp.cp(
        tinyLngSrcPath,
        path.join(tinyVendorPath, 'langs'),
        { preserveTimestamps: true, recursive: true }
      ));
    }

    // Update the XML file for tinyMCE
    const xmlVersionStr = /(<version>)(.+)(<\/version>)/;
    const xmlPath = path.join(rootPath, '/plugins/editors/tinymce/tinymce.xml');
    const xmlPromise = fsp.readFile(xmlPath).then((srcContent) => {
      const content = srcContent.toString().replace(xmlVersionStr, `$1${version}$3`);
      return fsp.writeFile(xmlPath, content, { encoding: 'utf8', mode: 0o644 });
    });
    promises.push(xmlPromise);

    // Copy Joomla template snippets which is in vendor folder
    // @TODO: the templates should be under plugin folder for better consistency?
    const tmplSrcPath = path.join(path.dirname(this.basePath), 'vendor', 'tinymce', 'templates');
    const tmplTargetPath = path.join(tinyVendorPath, 'templates');
    promises.push(fsp.cp(
      tmplSrcPath,
      tmplTargetPath,
      { preserveTimestamps: true, recursive: true }
    ));

    return Promise.all(promises);
  }
};
