/**
 * Assets Builder
 */
import path from 'node:path';
import fsp from "node:fs/promises";
import fs from "node:fs";

import pkgOptions from '../../package.json' with { type: 'json' };
import buildSettings from '../../build/build-modules-js/settings.json' with { type: 'json' };
import DefaultModuleBuilder from '../../build/build-modules-js/builder/default-module-builder.mjs';
import { resolvePackageFile } from '../../build/build-modules-js/utils/resolve-package.mjs';
import { handleJSFile } from '../../build/build-modules-js/javascript/js-handle.mjs';

/**
 * Copy package files.
 *
 * @param {Object} vendor           Vendor info from settings.json
 * @param {String} packageName      Original package name. This may be different from vendor.name.
 * @param {String} mediaVendorPath  Full path to /media/vendor
 *
 * @returns { Promise }
 */
const copyVendorFiles = async (vendor, packageName, mediaVendorPath) => {
  const vendorName = vendor.name || packageName;
  const modulePathJson = resolvePackageFile(path.join(packageName, 'package.json'));

  if (!modulePathJson) {
    throw new Error(`Package "${packageName}" not found`);
  }

  const modulePathRoot = path.dirname(modulePathJson);
  const modulePathTarget = path.join(mediaVendorPath, vendorName);

  // Make sure target folder exists
  if (!fs.existsSync(modulePathTarget)) {
    fs.mkdirSync(modulePathTarget);
  }

  // Check files that need to copy
  const promises = [];
  ['js', 'css', 'filesExtra'].forEach((type) => {
    if (!vendor[type]) return;
    const files = vendor[type];

    for (const srcFile in files) {
      promises.push(fsp.cp(
        path.join(modulePathRoot, srcFile),
        path.join(modulePathTarget, files[srcFile]),
        { preserveTimestamps: true, recursive: true }
      ));
    }
  });

  // Copy the license if exists
  const licensePath = vendor.licenseFilename ? resolvePackageFile(path.join(packageName, vendor.licenseFilename)) : false;
  if (licensePath) {
    promises.push(fsp.cp(licensePath, path.join(modulePathTarget, vendor.licenseFilename), { preserveTimestamps: true }));
  }

  return Promise.all(promises);
};

/**
 * Create Asset entries for the Registry
 *
 * @param {Object} vendor           Vendor info from settings.json
 * @param {String} packageName      Original package name. This may be different from vendor.name.
 *
 * @returns { Promise<[]> }       List of asset entries for the vendor.
 */
const prepareVendorAssets = async (vendor, packageName) => {
  if (!vendor.provideAssets || !vendor.provideAssets.length) {
    return [];
  }

  const vendorName = vendor.name || packageName;
  const modulePathJson = resolvePackageFile(path.join(packageName, 'package.json'));

  if (!modulePathJson) {
    throw new Error(`Package "${packageName}" not found`);
  }

  const moduleOptions = await import(modulePathJson, { with: { type: 'json' } });

  const entries = [];
  vendor.provideAssets.forEach((assetInfo) => {
    const entryBase = {
      package: packageName,
      name: assetInfo.name || vendorName,
      version: moduleOptions.default.version,
      type: assetInfo.type,
    };

    const entry = Object.assign(assetInfo, entryBase);

    // Update path to file
    if (assetInfo.uri && (assetInfo.type === 'script' || assetInfo.type === 'style' || assetInfo.type === 'webcomponent')) {
      let itemPath = assetInfo.uri;

      // Check for external path
      if (!itemPath.startsWith('http://') && !itemPath.startsWith('https://') && !itemPath.startsWith('//')) {
        itemPath = `vendor/${vendorName}/${itemPath}`;
      }

      entry.uri = itemPath;
    }

    entries.push(entry);
  });

  return entries;
};

export default class VendorModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Copy all vendor files according to Joomla's specs from build/settings.json
   * And create vendor/joomla.asset.json registry.
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

    const promises = [];
    // Store entries as object to keep sorting steady
    const assets = {};

    // Loop the vendors
    Object.keys(buildSettings.settings.vendors).forEach((packageName) => {
      const vendor = buildSettings.settings.vendors[packageName];
      const vendorName = vendor.name || packageName;
      // Keep sorting steady
      assets[vendorName] = [];

      promises.push(
        copyVendorFiles(vendor, packageName, this.targetPath)
          .then(() => prepareVendorAssets(vendor, packageName))
          .then((entries) => {
            if (!entries.length) return;
            assets[vendorName] = entries;
          })
      );
    });

    // Prepare registry data
    const registry = {
      $schema: 'https://developer.joomla.org/schemas/json-schema/web_assets.json',
      name: pkgOptions.name,
      version: pkgOptions.version,
      description: pkgOptions.description,
      license: pkgOptions.license,
      assets: [],
    };

    return Promise.all(promises).then(() => {
      // Finalize and save the Registry file
      const assetsArray = [];

      // Transform from Object to List
      Object.keys(assets).forEach((vendorName) => {
        assetsArray.push(...assets[vendorName]);
      });

      registry.assets = assetsArray;

      return fsp.writeFile(
        path.join(this.targetPath, 'joomla.asset.json'),
        JSON.stringify(registry, null, 2),
        { encoding: 'utf8', mode: 0o644 }
      );
    });
  }

  /**
   * Process CSS files
   * @returns { Promise }
   */
  async css() {
    // Nothing to do here
  }

  /**
   * Process JavaScript files and Modules
   * @returns { Promise }
   */
  async js() {
    // Minify few non minified files
    const folders = [
      'diff/js',
      'es-module-shims/js',
      'qrcode/js',
    ];

    const promises = [];
    folders.forEach((folder) => {
      const basePath = path.join(this.targetPath, folder);

      promises.push(
        fsp.readdir(basePath, { recursive: false, withFileTypes: true })
          .then((files) => {
            const jsFiles = [];

            files.forEach((file) => {
              if (!file.isFile()) return;

              const baseName = file.name;
              const ext = path.extname(baseName);
              const fullSrcPath = path.join(file.parentPath, file.name);

              if (ext !== '.js' || baseName.endsWith('.min.js')) return;

              jsFiles.push(handleJSFile(
                fullSrcPath,
                fullSrcPath
              ));
            });

            return Promise.all(jsFiles);
          })
      );
    });

    return Promise.all(promises);
  }

  /**
   * Watch handler
   * @returns { Promise }
   */
  async watch() {
    throw new Error(`Watch is not implemented for [${this.name}]`);
  }
};
