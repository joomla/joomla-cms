import { stat, readFile, readdir, writeFile } from 'node:fs/promises';
import { copyFileSync, existsSync, mkdirSync, rmSync } from 'node:fs';
import { join, extname, sep } from 'node:path';

import { localisePackages } from './localise-packages.mjs';
import { minifyVendor } from './minify-vendor.mjs';
import { patchPackages } from './patches.mjs';
import { cleanVendors } from './cleanup-media.mjs';
import { cssVersioningVendor } from '../stylesheets/css-versioning.mjs';

const RootPath = process.cwd();
const knownDirs = [
  'templates/site/cassiopeia',
  'templates/administrator/atum',
];

/**
 * Will scan all the installed extensions and rebuild the cleanUpFolders registry.
 */
const updateSettings = async (options) => {
  const extensionsScanned = await readdir(`${RootPath}/build/media_source`, { withFileTypes: true });
  const extensions = [...extensionsScanned]
    .filter((x) => !['.DS_Store', 'templates', 'vendor', 'cache'].includes(x.name) && x.isDirectory())
    .map((x) => x.name);

  options.settings.cleanUpFolders = [...extensions, ...knownDirs];
};

/**
 * Method to recreate the basic media folder structure
 * After execution the media folder is populated with empty js and css subdirectories
 * images subfolders with their relative files and any other files except .js, .css
 *
 * @returns {Promise}
 */
export const recreateMediaFolder = async (options) => {
  await updateSettings(options);
  const installedVendors = Object.keys(options.settings.vendors).map((vendor) => {
    if (vendor === 'choices.js') {
      return 'vendor/choicesjs';
    }
    if (vendor === '@fortawesome/fontawesome-free') {
      return 'vendor/fontawesome-free';
    }
    if (vendor === '@claviska/jquery-minicolors') {
      return 'vendor/minicolors';
    }
    if (vendor === '@webcomponents/webcomponentsjs') {
      return 'vendor/webcomponentsjs';
    }
    if (vendor === 'joomla-ui-custom-elements') {
      return 'vendor/joomla-custom-elements';
    }
    return `vendor/${vendor}`;
  });

  // Clean up existing folders
  [...options.settings.cleanUpFolders, ...installedVendors].forEach((folder) => {
    const folderPath = join(`${RootPath}/media`, folder);
    if (existsSync(folderPath)) {
      rmSync(folderPath, { recursive: true, force: true });
      mkdirSync(folderPath, { recursive: true, mode: 0o755 });
    }
  });

  console.log('Recreating the media folder...');

const entries = (await readdir('build/media_source', { recursive: true })).map((fileName) => join('build/media_source', fileName)).filter((file) => !file.endsWith('.DS_Store'));
  for (const entry of entries) {
    const fullPath = entry.replace(`build${sep}media_source`, 'media');
    if (entry && !existsSync(fullPath) && (await stat(entry)).isDirectory()) {
      mkdirSync(fullPath, { recursive: true, mode: 0o755 });
      continue;
    }
    if (entry && (await stat(entry)).isFile() && (extname(entry) === '.js' || extname(entry) === '.css')) {
      continue;
    }
    if (entry && (await stat(entry)).isFile()) {
      copyFileSync(entry, fullPath);
    }
  }

  const mediaTemplatesPath = join(RootPath, 'media/templates');
  const SCSSMediafolders = (await readdir(mediaTemplatesPath, { recursive: true }))
    .filter((file) => extname(file) === '.scss')
    .map((file) => join(mediaTemplatesPath, file));

  // Patch the scss files
  Object.keys(SCSSMediafolders).forEach(async (file) => {
    const contents = await readFile(SCSSMediafolders[file], 'utf8');
    // Transform this `../../../../../../media/` to `../../../../`
    await writeFile(SCSSMediafolders[file], contents.replace(/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/media\//g, '../../../../'));
  });

  await localisePackages(options);
  await patchPackages(options);
  await cleanVendors();
  await minifyVendor();
  await cssVersioningVendor();

  return entries;
};
