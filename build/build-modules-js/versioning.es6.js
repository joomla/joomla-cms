const {
  lstat, readdir, readFile, writeFile,
} = require('fs-extra');
const {
  basename, dirname, resolve, sep,
} = require('path');
const { createHashFromFile } = require('./utils/hashfromfile.es6.js');
const { Timer } = require('./utils/timer.es6.js');

const RootPath = process.cwd();
const exclusion = [
  // We will skip these:
  '.DS_Store',
  'index.html',
  'cache',
  'vendors',
];
const final = {};

/**
 * Update a given joomla.assets.json entry
 *
 * @param asset
 * @param directory
 * @returns {Promise<{type}|*>}
 */
const updateAsset = async (asset, directory) => {
  const currentDir = `${RootPath}${sep}media${sep}${directory}`;
  if (!asset.type) {
    final[directory].push(asset);
    return;
  }
  let subDir;
  if (asset.type === 'script') {
    subDir = 'js';
  }
  if (asset.type === 'style') {
    subDir = 'css';
  }
  if (!subDir) {
    final[directory].push(asset);
    return;
  }

  let path = `${currentDir}${sep}${subDir}${sep}${basename(asset.uri)}`;
  if (`${directory}/${basename(asset.uri)}` !== asset.uri) {
    if (dirname(asset.uri) === 'system/fields') {
      path = `${currentDir}${sep}${subDir}${sep}fields${sep}${basename(asset.uri)}`;
    } else {
      final[directory].push(asset);
      return;
    }
  }

  const jAssetFile = await lstat(path);

  if (!jAssetFile.isFile()) {
    final[directory].push(asset);
    return;
  }

  const hash = await createHashFromFile(path);

  asset.version = hash.substring(0, 6);
  final[directory].push(asset);
};

/**
 * Read the joomla.assets.json and loop the assets
 *
 * @param directory
 * @returns {Promise<void>}
 */
const fixVersion = async (directory) => {
  let jAssetFile;
  try {
    jAssetFile = await lstat(`${RootPath}${sep}media${sep}${directory}${sep}joomla.asset.json`);
  } catch (err) {
    return;
  }

  if (!jAssetFile.isFile()) {
    return;
  }

  const jAssetFileContent = await readFile(`${RootPath}${sep}media${sep}${directory}${sep}joomla.asset.json`, { encoding: 'utf8' });
  let jsonData;
  try {
    jsonData = JSON.parse(jAssetFileContent);
  } catch (err) {
    throw new Error(`media\\${directory}\\joomla.asset.json is not a valid JSON file!!!`);
  }

  if (!jsonData || !jsonData.assets.length) {
    return;
  }

  const processes = [];
  jsonData.assets.map((asset) => processes.push(updateAsset(asset, directory)));

  await Promise.all(processes);

  jsonData.assets = final[directory];
  await writeFile(`${RootPath}${sep}media${sep}${directory}${sep}joomla.asset.json`, JSON.stringify(jsonData, '', 2), { encoding: 'utf8', mode: 0o644 });
};

/**
 * Loop the media folder and add version to all .js/.css entries in all
 * the joomla.assets.json files
 *
 * @returns {Promise<void>}
 */
module.exports.versioning = async () => {
  const bench = new Timer('Versioning');
  const tasks = [];
  let mediaDirectories = await readdir(resolve(RootPath, 'media'));
  mediaDirectories = mediaDirectories.filter((dir) => !exclusion.includes(dir));

  mediaDirectories.forEach((directory) => {
    final[directory] = [];
    tasks.push(fixVersion(directory));
  });

  await Promise.all(tasks);

  bench.stop();
};
