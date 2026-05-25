/**
 * Assets Builder
 */

import path from 'node:path';
import fsp from "node:fs/promises";
import fs from "node:fs";
import { createRequire } from 'node:module';
import DefaultModuleBuilder from '../../build/build-modules-js/builder/default-module-builder.mjs';
import { resolvePackageFile, getPackagesUnderScope } from '../../build/build-modules-js/utils/resolve-package.mjs';
import { handleMJSFile } from '../../build/build-modules-js/javascript/js-handle.mjs';

/**
 * Update joomla.asset.json for codemirror
 *
 * @param { [['module', 'path']] } modules
 * @param { string[] }externalModules
 * @param { string } basePath
 * @param { string } targetPath
 * @return {Promise<void>}
 */
const updateAssetRegistry = async (modules, externalModules, basePath, targetPath) => {
  const srcPath = path.join(basePath, 'joomla.asset.json');
  const destPath = path.join(targetPath, 'joomla.asset.json');
  const require = createRequire(import.meta.url);

  // Get base JSON and update
  const registry = JSON.parse(fs.readFileSync(srcPath, { encoding: 'utf8' }));

  // Add dependencies to base codemirror asset
  registry.assets.forEach((asset) => {
    if (asset.name === 'codemirror' && asset.type === 'script') {
      asset.dependencies = externalModules;
    }
  });

  // Create asset for each module
  modules.forEach(([module, modulePath]) => {
    const modulePathJson = resolvePackageFile(path.join(module, 'package.json'));
    const moduleOptions = require(modulePathJson);
    const asset = {
      type: 'script',
      name: module,
      uri: modulePath.replace('.js', '.min.js').replace(/\\/g, '/'),
      importmap: true,
      version: moduleOptions.version,
    };

    registry.assets.push(asset);
  });

  // Write assets registry
  return fsp.writeFile(destPath, JSON.stringify(registry, null, 2), { encoding: 'utf8', mode: 0o644 });
};

/**
 * Copy/Prepare Codemirror modules
 * @param { string } basePath
 * @param { string } targetPath
 * @return {Promise<void>}
 */
const compileCodemirror = async (basePath, targetPath) => {
  const cmModules = getPackagesUnderScope('@codemirror');
  const lModules = getPackagesUnderScope('@lezer');
  const externalModules = [...cmModules, ...lModules];
  const destBasePath = 'media/vendor/codemirror/js';
  const tasks = [];
  const modules = [];

  // Collect @codemirror modules
  cmModules.forEach((module) => {
    const destFile = `${module.replace('@codemirror/', 'codemirror-')}.js`;
    const destPath = path.join(destBasePath, destFile);

    modules.push([module, destPath]);
  });

  // Collect @lezer modules which @codemirror depends on
  lModules.forEach((module) => {
    const destFile = `${module.replace('@lezer/', 'lezer-')}.js`;
    const destPath = path.join(destBasePath, destFile);

    modules.push([module, destPath]);
  });

  // Copy/Compile the modules
  modules.forEach(([ module, destPath ]) => {
    tasks.push(handleMJSFile(module, destPath));
  });

  return Promise.all(tasks).then(() => {
    return updateAssetRegistry(modules, externalModules, basePath, targetPath);
  });
};

export default class CodemirrorModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Remove files associated with the builder
   * @returns { Promise }
   */
  async clear() {
    await super.clear();

    const vendorPath = 'media/vendor/codemirror';

    if (!fs.existsSync(vendorPath)) {
      return;
    }

    return fsp.rm(vendorPath, { recursive: true });
  }

  /**
   * Copy files to target location, including the codemirror license file.
   *
   * @returns { Promise }
   */
  async copy() {
    await super.copy();

    const require = createRequire(import.meta.url);
    const buildSettings = require('../../build/build-modules-js/settings.json');
    const licenseFilename = buildSettings.settings?.vendors?.['@codemirror/view']?.licenseFilename;

    if (!licenseFilename) {
      return;
    }

    const licenseSrc = resolvePackageFile(path.join('@codemirror/view', licenseFilename));

    if (!licenseSrc) {
      return;
    }

    return fsp.cp(licenseSrc, path.join(this.targetPath, licenseFilename), { preserveTimestamps: true });
  }

  /**
   * Process JavaScript files and Modules
   * @returns { Promise }
   */
  async js() {
    await super.js();

    return compileCodemirror(this.basePath, this.targetPath);
  }
};
