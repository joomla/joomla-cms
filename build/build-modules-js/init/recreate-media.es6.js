const {
  stat, copy, existsSync, emptyDirSync,
} = require('fs-extra');
const {
  readFile, writeFile, readdir,
} = require('fs').promises;
const { join, extname } = require('path');
const recursive = require('recursive-readdir');

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
module.exports.recreateMediaFolder = async (options) => {
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
      emptyDirSync(folderPath);
    }
  });

  // eslint-disable-next-line no-console
  console.log('Recreating the media folder...');

  const filterFunc = async (src) => {
    const fileStat = await stat(src);
    if (fileStat.isFile() && (extname(src) === '.js' || extname(src) === '.css')) {
      return false;
    }

    return true;
  };

  await copy(join(RootPath, 'build/media_source'), join(RootPath, 'media'), { filter: filterFunc, preserveTimestamps: true });

  const SCSSMediafolders = await recursive(join(RootPath, 'media/templates'), ['!*.+(scss)']);

  // Patch the scss files
  Object.keys(SCSSMediafolders).forEach(async (file) => {
    const contents = await readFile(SCSSMediafolders[file], 'utf8');
    // Transform this `../../../../../../media/` to `../../../../`
    await writeFile(SCSSMediafolders[file], contents.replace(/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/media\//g, '../../../../'));
  });
};
