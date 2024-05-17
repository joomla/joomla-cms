const fs = require('fs');
const fspath = require('path');

/**
 * Deletes a folder with the given path recursive.
 *
 * @param {string} path The path
 * @param {object} config The config
 *
 * @returns null
 */
function deleteFolder(path, config) {
  fs.rmSync(`${config.env.cmsPath}/${path}`, { recursive: true, force: true });

  return null;
}

/**
 * Writes the given content to a file for the given path.
 *
 * @param {string} path The path
 * @param {mixed} content The content
 * @param {object} config The config
 *
 * @returns null
 */
function writeFile(path, content, config) {
  fs.mkdirSync(fspath.dirname(`${config.env.cmsPath}/${path}`), { recursive: true, mode: 0o777 });
  fs.chmod(fspath.dirname(`${config.env.cmsPath}/${path}`), 0o777);
  fs.writeFileSync(`${config.env.cmsPath}/${path}`, content);
  fs.chmod(`${config.env.cmsPath}/${path}`, 0o777);

  return null;
}

/**
 * Get file permissions.
 *
 * @param {string} path The file path
 *
 * @returns string e.g. '644'
 */
function getFilePermissions(path) {
  const stats = fs.statSync(path);
  const modeStr = stats.mode.toString(8);
  return modeStr.slice(-3);
}

/**
 * Change file permissions.
 *
 * @param {string} path The file path
 * @param {string} mode file mode, e.g. '644'
 *
 * @returns null to indicate success
 */
function changeFilePermissions(path, mode) {
  fs.chmodSync(path, mode);
  return null;
}

module.exports = { writeFile, deleteFolder, getFilePermissions, changeFilePermissions };
