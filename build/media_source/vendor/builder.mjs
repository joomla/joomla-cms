/**
 * Assets Builder
 */
import path from 'node:path';
import fsp from "node:fs/promises";
import fs from "node:fs";

import pkgOptions from '../../../package.json' with { type: 'json' };
import buildSettings from '../../build-modules-js/settings.json' with { type: 'json' };
import DefaultModuleBuilder from '../../build-modules-js/builder/default-module-builder.mjs';
import { resolvePackageFile } from '../../build-modules-js/utils/resolve-package.mjs';

/**
 * Method to resolve each vendor package.
 * Copy package files and prepare data for the registry for the package.
 *
 * @param {Object} vendor           Vendor info from settings.json
 * @param {String} packageName      Original package name. This may be different from vendor.name.
 * @param {String} mediaVendorPath  Full path to /media/vendor
 * @param {Object} options
 * @param {Object} registry
 *
 * @returns { Promise }
 */
const prepareVendorPackage = async (vendor, packageName, mediaVendorPath, options11, registry11) => {
  const vendorName = vendor.name || packageName;
  const modulePathJson = resolvePackageFile(path.join(packageName, 'package.json'));

  if (!modulePathJson) {
    throw new Error(`Package "${packageName}" not found`);
  }

  const modulePathRoot = path.dirname(modulePathJson);
  const modulePathTarget = path.join(mediaVendorPath, vendorName);
  const moduleOptions = await import(modulePathJson, { with: { type: 'json' } });

  if (packageName === 'tinymce') {
    console.warn('Skipping tinymce!!!1111111 Fix me!!!!111');
    return;
  }

  if (!fs.existsSync(modulePathTarget)) {
    fs.mkdirSync(modulePathTarget);
  }

  // Check files that need to copy
  const promises = [];
  ['js', 'css', 'filesExtra'].forEach((type) => {
    if (!vendor[type]) return;
    const files = vendor[type];

    for (const srcFile in files) {
      promises.push(fsp.cp(path.join(modulePathRoot, srcFile), path.join(modulePathTarget, files[srcFile]), { preserveTimestamps: true }));
    }
  });

  console.log(modulePathTarget);
};

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

  /**
   * Copy all vendor files according to Joomla's specs from build/settings.json
   *
   * @returns { Promise }
   */
  async copy() {
    const modulesSrcBasePath = path.join(process.cwd(), 'node_modules');

    // This should never happen
    if (!fs.existsSync(modulesSrcBasePath)) {
      throw new Error(`Modules source "${modulesSrcBasePath}" not found`);
    }

    // Prepare target
    if (!fs.existsSync(this.targetPath)) {
      fs.mkdirSync(this.targetPath, { recursive: true, mode: 0o755});
    }

    // Prepare registry data
    const registry = {
      $schema: 'https://developer.joomla.org/schemas/json-schema/web_assets.json',
      name: pkgOptions.name,
      version: pkgOptions.version,
      description: pkgOptions.description,
      license: pkgOptions.license,
      assets: [],
    };
    const promises = [];

    // Loop the vendors
    for (const packageName in buildSettings.settings.vendors) {
      const vendor = buildSettings.settings.vendors[packageName];

      promises.push(prepareVendorPackage(vendor, packageName, this.targetPath, pkgOptions, registry));
      break;
    }

  }

  /**
   * Process CSS files
   * @returns { Promise }
   */
  async css() {}

  /**
   * Process JavaScript files and Modules
   * @returns { Promise }
   */
  async js() {}
};
